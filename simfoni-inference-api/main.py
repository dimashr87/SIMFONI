"""
SIMFONI Inference API
======================
Load model setiap aplikasi start (bukan setiap request)

Run:
    uvicorn main:app --host 0.0.0.0 --port 8001
"""

import re
import time
import logging
from typing import Literal
from urllib.parse import urlparse
from contextlib import asynccontextmanager
from datetime import date

import requests
import torch
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from pydantic_settings import BaseSettings
from transformers import (
    AutoModelForSequenceClassification,
    AutoModelForSeq2SeqLM,
    AutoTokenizer,
)

import mediacloud.api
import mediacloud.error
from newspaper import Article, Config

logging.basicConfig(level=logging.INFO, format="%(asctime)s  %(levelname)-8s %(message)s", datefmt="%H:%M:%S")
logger = logging.getLogger("simfoni-api")

# Konfigurasi dari .env
class Settings(BaseSettings):
    # sumber model: folder lokal ("./models/...") atau repo Hugging Face ("akun/repo"),
    # bisa dikunci ke versi tertentu dengan "akun/repo@commit"
    model_wilayah_path: str
    model_indikator_path: str
    indobart_model_path: str = "./models/indobart-merged-step2-bps"
    idt5_model_path: str = "./models/idt5-merged-step2-bps"
    hf_token: str | None = None
    hf_offline: bool = False
    max_input_length: int = 512
    max_target_length: int = 128
    num_beams: int = 4
    device: str = "cpu"

    gemini_api_key: str = ""
    gemini_model: str = "gemini-3.5-flash-lite"

    mediacloud_api_key: str = ""
    mediacloud_collection_id: int | None = None
    mediacloud_query: str = ""

    class Config:
        env_file = ".env"
        extra = "ignore"

settings = Settings()
device = torch.device(settings.device if torch.cuda.is_available() or settings.device == "cpu" else "cpu") # cek gpu


# model peringkasan: 2 model hasil fine-tuning + Gemini (API)
ModelRingkasan = Literal["indobart", "idt5", "gemini"]
MODEL_RINGKASAN: tuple[str, ...] = ("indobart", "idt5", "gemini")


# Clean text classification & Summarization
def clean_text_classification(text: str) -> str:
    """Dipakai untuk model IndoBERT (wilayah & indikator)."""
    if not isinstance(text, str):
        return ""
    text = re.sub(r"&#[0-9]+;", " ", text)
    text = re.sub(r"&[a-zA-Z]+;", " ", text)
    text = re.sub(r"<[^>]+>", " ", text)
    text = re.sub(r"http\S+|www\.\S+", " ", text)
    text = re.sub(r"[^\w\s.,!?;:\-'\"()]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()
    text = text.lower()
    return text

def clean_text_summarization(text) -> str:
    """Dipakai untuk model idT5/IndoBART. Harus identik dengan clean_text()
    di notebook fine-tuning Step 1 (Liputan6) -- termasuk penghapusan prefix
    'liputan6.com, kota:', author tag, dan keterangan gambar."""
    if isinstance(text, list) and len(text) > 0 and isinstance(text[0], list):
        text = " ".join([" ".join(sent) for sent in text])
    elif isinstance(text, list):
        text = " ".join(text)

    if not isinstance(text, str):
        return ""

    text = re.sub(r"^liputan6\s*[\.,]\s*com\s*,\s*[a-zA-Z\s]+\s*[:\-]\s*", "", text, flags=re.IGNORECASE)
    text = re.sub(r"\s*\([^)]+\)\s*\.?\s*$", "", text)
    text = re.sub(r"\s*\([^)]+/[^)]+\)\s*", " ", text)
    text = re.sub(r"\s*\([^)]*(liputan\s*6|sctv|idn\s*times|antara|foto)[^)]*\)\s*", " ", text, flags=re.IGNORECASE)
    text = re.sub(r"^Ilustrasi[^)]+\)\s*", "", text, flags=re.IGNORECASE)

    text = text.lower()

    text = re.sub(r"&#[0-9]+;", " ", text)
    text = re.sub(r"&[a-zA-Z]+;", " ", text)
    text = re.sub(r"<[^>]+>", " ", text)
    text = re.sub(r"http\S+|www\.\S+", " ", text)
    text = re.sub(r"[^\w\s.,!?;:\-'\"()%]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()

    return text


# container model
class ModelBundle: # container untuk menyimpan semua model dan tokenizer
    wilayah_tokenizer = None
    wilayah_model = None
    indikator_tokenizer = None
    indikator_model = None
    summary_tokenizers: dict = {}  # {"indobart": tokenizer, "idt5": tokenizer}
    summary_models: dict = {}      # {"indobart": model, "idt5": model}

models = ModelBundle()

def _sumber_model(nilai: str) -> dict:
    repo, _, revisi = nilai.partition("@")
    return {
        "pretrained_model_name_or_path": repo,
        "revision": revisi or None,
        "token": settings.hf_token,
        "local_files_only": settings.hf_offline,
    }

@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info(f"Loading models ke device: {device}" + (" (mode offline: hanya cache)" if settings.hf_offline else ""))

    logger.info(f"Loading model wilayah dari {settings.model_wilayah_path} ...")
    models.wilayah_tokenizer = AutoTokenizer.from_pretrained(**_sumber_model(settings.model_wilayah_path))
    models.wilayah_model = AutoModelForSequenceClassification.from_pretrained(
        **_sumber_model(settings.model_wilayah_path)
    ).to(device).eval()

    logger.info(f"Loading model indikator dari {settings.model_indikator_path} ...")
    models.indikator_tokenizer = AutoTokenizer.from_pretrained(**_sumber_model(settings.model_indikator_path))
    models.indikator_model = AutoModelForSequenceClassification.from_pretrained(
        **_sumber_model(settings.model_indikator_path)
    ).to(device).eval()

    for nama, path in (("indobart", settings.indobart_model_path), ("idt5", settings.idt5_model_path)):
        logger.info(f"Loading model ringkasan {nama} dari {path} ...")
        models.summary_tokenizers[nama] = AutoTokenizer.from_pretrained(**_sumber_model(path))
        models.summary_models[nama] = AutoModelForSeq2SeqLM.from_pretrained(**_sumber_model(path)).to(device).eval()
        models.summary_models[nama].generation_config.max_length = None

    if not settings.gemini_api_key:
        logger.warning("GEMINI_API_KEY belum diisi di .env -- ringkasan Gemini akan gagal.")

    logger.info("Semua model berhasil dimuat.")
    yield
    logger.info("Shutting down, membersihkan model dari memori.")
    models.wilayah_model = models.indikator_model = None
    models.summary_models = {}

app = FastAPI(title="SIMFONI Inference API", lifespan=lifespan)


### SCHEMAS
class ClassifyRequest(BaseModel): # klasifikasi wilayah/indikator
    text: str = Field(..., min_length=1, description="Judul + konten berita")

class ClassifyResponse(BaseModel): # hasil klasifikasi wilayah/indikator
    label: str
    confidence: float
    probs: dict[str, float]

class SummarizeSDSRequest(BaseModel): # ringkasan SDS
    text: str = Field(..., min_length=1)
    model: ModelRingkasan = "idt5"

class SummarizeMDSRequest(BaseModel): # ringkasan MDS (gabungan beberapa ringkasan SDS)
    texts: list[str] = Field(..., min_length=1, description="Kumpulan ringkasan SDS untuk digabung")
    model: ModelRingkasan = "idt5"

class SummarizeResponse(BaseModel): # hasil ringkasan SDS/MDS
    summary: str
    model: str
    runtime_ms: float

class ProcessArticleRequest(BaseModel): # tes manual 1 artikel
    text: str = Field(..., min_length=1)

class ProcessArticleResponse(BaseModel): # hasil klasifikasi + ringkasan untuk 1 artikel
    wilayah: ClassifyResponse
    indikator: ClassifyResponse | None = None          # null bila NON_DKI
    ringkasan: dict[str, str | None] | None = None     # {"indobart": ..., "idt5": ..., "gemini": ...}, null bila NON_DKI
    pesan_error: str | None = None                     # model yang gagal meringkas, bila ada

class ScrapedArticle(BaseModel): # scraped article, klasifikasi, ringkasan, disimpan di db
    judul: str
    konten: str
    url: str
    sumber: str | None = None
    tanggal_berita: str  # format YYYY-MM-DD
    wilayah_label: str
    wilayah_confidence: float
    indikator_label: str | None = None
    indikator_confidence: float | None = None
    ringkasan_indobart: str | None = None
    ringkasan_idt5: str | None = None
    ringkasan_gemini: str | None = None
    status: str  # "summarized" (DKI, semua model berhasil), "classified" (NON_DKI), atau "failed"
    pesan_error: str | None = None  # bagian yang gagal bila status "failed"

class BeritaMediaCloud(BaseModel): # simpan berita yang belum diekstrak
    url: str
    judul: str
    sumber: str | None = None
    tanggal_berita: str  # format YYYY-MM-DD

class ProsesBeritaResponse(BaseModel): # hasil proses 1 berita
    status: str                           # "summarized", "classified", "failed", "gagal_ekstrak"
    berita: ScrapedArticle | None = None  # null bila gagal_ekstrak

class CariBeritaRequest(BaseModel): # request body untuk cari berita 1 tanggal
    tanggal: date = Field(..., description="YYYY-MM-DD, tanggal berita (WIB), dihitung oleh Laravel")
    query: str | None = Field(None, description="Query Media Cloud dari pengaturan keyword SIMFONI; kosong = MEDIACLOUD_QUERY di .env")

## fungsi klasifikasi & ringkasan
def _classify(text: str, tokenizer, model) -> ClassifyResponse: # fungsi klasifikasi
    clean = clean_text_classification(text)
    inputs = tokenizer(
        clean, truncation=True, max_length=512, return_tensors="pt", padding=True
    ).to(device)

    with torch.no_grad():
        outputs = model(**inputs)

    probs = torch.softmax(outputs.logits, dim=-1).squeeze().cpu().numpy()
    pred = int(probs.argmax())
    id2label = model.config.id2label

    return ClassifyResponse( # label, confidence, dan semua probabilitas tiap label
        label=id2label[pred],
        confidence=float(probs[pred]),
        probs={id2label[i]: float(probs[i]) for i in range(len(probs))},
    )

def _generate_summary(text: str, model: str) -> str: # ringkasan dengan model lokal (indobart / idt5)
    clean = clean_text_summarization(text)
    if model == "idt5":
        clean = "summarize: " + clean   # prefix idT5, IndoBART tidak pakai prefix

    tokenizer = models.summary_tokenizers[model]
    inputs = tokenizer(
        clean, max_length=settings.max_input_length, truncation=True, return_tensors="pt"
    ).to(device)

    with torch.no_grad():
        output_ids = models.summary_models[model].generate(
            **inputs,
            max_new_tokens=settings.max_target_length,
            num_beams=settings.num_beams,
            early_stopping=True,
            no_repeat_ngram_size=3
        )

    return tokenizer.decode(output_ids[0], skip_special_tokens=True)
    # return hasil decode ringkasan tanpa special token

PROMPT_SDS = (
    "Ringkas berita berbahasa Indonesia berikut menjadi 2-3 kalimat (paling banyak 60 kata). "
    "Gunakan hanya informasi yang ada di berita, tanpa opini atau informasi tambahan. "
    "Tulis ringkasannya saja, tanpa judul atau kalimat pengantar.\n\nBerita:\n{teks}"
)

PROMPT_MDS = (
    "Berikut adalah ringkasan dari beberapa berita. Buat satu ringkasan gabungan dalam 1 paragraf "
    "(paling banyak 120 kata) yang menjelaskan fenomena atau isu utama yang dibahas, dengan "
    "menggabungkan informasi dari berbagai berita. Gunakan hanya informasi dari ringkasan di bawah, "
    "tanpa opini atau informasi tambahan. Tulis ringkasannya saja, tanpa judul atau kalimat "
    "pengantar.\n\n{teks}"
)

GEMINI_TIMEOUT_SDS = 20                     # detik per percobaan: 3 x 20 + 7 = 67 dtk
GEMINI_TIMEOUT_MDS = 35                     # detik per percobaan: 3 x 35 + 7 = 112 dtk (dashboard)
GEMINI_JEDA_COBA_ULANG = (2, 5)             # jeda sebelum percobaan ke-2 dan ke-3
GEMINI_STATUS_SEMENTARA = {429, 500, 502, 503, 504}  # kuota per menit

def _gemini(prompt: str, timeout: int = GEMINI_TIMEOUT_SDS) -> str: # ringkasan dengan Gemini lewat REST API
    if not settings.gemini_api_key:
        raise RuntimeError("GEMINI_API_KEY belum diisi di .env")

    url = f"https://generativelanguage.googleapis.com/v1beta/models/{settings.gemini_model}:generateContent"
    body = {
        "contents": [{"parts": [{"text": prompt}]}],
        "generationConfig": {"temperature": 0.2, "maxOutputTokens": 1024},
    }

    # coba ulang bila timeout / koneksi putus / server sibuk / kuota per menit
    for percobaan, jeda in enumerate((*GEMINI_JEDA_COBA_ULANG, None), start=1):
        try:
            response = requests.post(
                url, json=body, headers={"x-goog-api-key": settings.gemini_api_key}, timeout=(10, timeout)
            )
        except (requests.Timeout, requests.ConnectionError) as e:
            masalah = f"Gemini tidak merespons ({type(e).__name__})"
        else:
            if response.status_code == 200:
                break
            masalah = f"Gemini HTTP {response.status_code}: {response.text[:200]}"
            if response.status_code not in GEMINI_STATUS_SEMENTARA:
                raise RuntimeError(masalah)

        if jeda is None:
            raise RuntimeError(f"{masalah}, setelah {percobaan} percobaan")
        logger.warning("Gemini percobaan %d gagal (%s), coba lagi dalam %d detik", percobaan, masalah[:120], jeda)
        time.sleep(jeda)

    parts = response.json().get("candidates", [{}])[0].get("content", {}).get("parts", [])
    teks = "".join(p.get("text", "") for p in parts if not p.get("thought")).strip()
    if not teks:
        raise RuntimeError("Gemini tidak mengembalikan teks ringkasan")
    return teks

def _ringkas(text: str, model: str) -> str: # SDS 1 berita dengan model yang dipilih
    if model == "gemini":
        return _gemini(PROMPT_SDS.format(teks=text[:15000]))
    return _generate_summary(text, model)

def _ringkas_mds(texts: list[str], model: str) -> str: # MDS beberapa ringkasan dengan model yang dipilih
    if model == "gemini":
        daftar = "\n".join(f"{i}. {t}" for i, t in enumerate(texts, start=1))
        return _gemini(PROMPT_MDS.format(teks=daftar), timeout=GEMINI_TIMEOUT_MDS)
    return _generate_summary(" ".join(texts), model)

def _ringkas_semua_model(text: str) -> tuple[dict[str, str | None], list[str]]: # SDS dengan 3 model
    """Return ringkasan per model."""
    ringkasan: dict[str, str | None] = {}
    errors: list[str] = []
    for model in MODEL_RINGKASAN:
        try:
            ringkasan[model] = _ringkas(text, model)
        except Exception as e:
            logger.error(f"Gagal meringkas dengan {model}: {e}")
            ringkasan[model] = None
            errors.append(f"{model}: {str(e)[:200]}")
    return ringkasan, errors


## ENDPOINTS
@app.get("/health") # cek status API
def health():
    return {
        "status": "ok",
        "device": str(device),
        "model_ringkasan": list(MODEL_RINGKASAN),
        "gemini_model": settings.gemini_model,
        "gemini_api_key_terisi": bool(settings.gemini_api_key),
        "sumber_model": {  # folder lokal atau repo Hugging Face (@versi) yang sedang dipakai
            "wilayah": settings.model_wilayah_path,
            "indikator": settings.model_indikator_path,
            "indobart": settings.indobart_model_path,
            "idt5": settings.idt5_model_path,
        },
        "hf_offline": settings.hf_offline,
    }

@app.post("/classify/wilayah", response_model=ClassifyResponse) # klasifikasi wilayah
def classify_wilayah(req: ClassifyRequest):
    return _classify(req.text, models.wilayah_tokenizer, models.wilayah_model)

@app.post("/classify/indikator", response_model=ClassifyResponse) # klasifikasi indikator
def classify_indikator(req: ClassifyRequest):
    return _classify(req.text, models.indikator_tokenizer, models.indikator_model)

@app.post("/summarize/sds", response_model=SummarizeResponse) # ringkasan SDS
def summarize_sds(req: SummarizeSDSRequest):
    start = time.perf_counter()
    try:
        summary = _ringkas(req.text, req.model)
    except RuntimeError as e:  # error Gemini
        raise HTTPException(status_code=502, detail=str(e))
    duration_ms = (time.perf_counter() - start) * 1000
    return SummarizeResponse(summary=summary, model=req.model, runtime_ms=duration_ms)

@app.post("/summarize/mds", response_model=SummarizeResponse) # ringkasan MDS
def summarize_mds(req: SummarizeMDSRequest):
    """Gabungan ringkasan SDS jadi satu ringkasan, dengan model yang dipilih"""
    start = time.perf_counter()
    try:
        summary = _ringkas_mds(req.texts, req.model)
    except RuntimeError as e:  # error Gemini
        raise HTTPException(status_code=502, detail=str(e))
    duration_ms = (time.perf_counter() - start) * 1000
    return SummarizeResponse(summary=summary, model=req.model, runtime_ms=duration_ms)

def _extract_full_text(url: str) -> str | None: # ambil konten berita dengan newspaper3k
    """Download artikel dengan header browser lalu parse menggunakan Newspaper3k.

    Banyak portal menolak user-agent default Newspaper3k dengan 403. Download
    dilakukan sendiri supaya timeout, redirect, header, dan status HTTP dapat
    dikendalikan sebelum HTML diberikan ke parser.
    """
    parsed_url = urlparse(url)
    if parsed_url.scheme not in {"http", "https"} or not parsed_url.netloc:
        logger.warning("URL artikel tidak valid: %s", url)
        return None

    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/131.0.0.0 Safari/537.36"
        ),
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Language": "id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7",
        "Referer": f"{parsed_url.scheme}://{parsed_url.netloc}/",
    }

    try:
        response = requests.get(url, headers=headers, timeout=20)
        response.raise_for_status()

        config = Config()
        config.browser_user_agent = headers["User-Agent"]
        article = Article(url, language="id", config=config)
        article.set_html(response.text)
        article.parse()
        text = article.text.strip()
        if len(text) < 100:   # terlalu pendek, failed ekstrak
            return None
        return text
    except requests.HTTPError as e:
        status = e.response.status_code if e.response is not None else "unknown"
        logger.warning("Portal menolak artikel %s dengan HTTP %s", url, status)
        return None
    except requests.RequestException as e:
        logger.warning("Gagal mengunduh artikel %s: %s", url, e)
        return None
    except Exception as e:
        logger.warning(f"Gagal ekstrak teks dari {url}: {e}")
        return None

RETRY_MEDIA_CLOUD = (10, 20, 40)  # mengatasi request limit media cloud

def _story_list(mc_search, *args, **kwargs): # coba ulang saat request limit
    for jeda in (*RETRY_MEDIA_CLOUD, None):
        try:
            return mc_search.story_list(*args, **kwargs)
        except mediacloud.error.APIResponseError as e:
            if e.response.status_code != 429 or jeda is None:
                raise
            retry_after = e.response.headers.get("Retry-After", "")
            tunggu = int(retry_after) if retry_after.isdigit() else jeda
            logger.warning("Media Cloud rate limited (429), menunggu %d detik lalu coba lagi...", tunggu)
            time.sleep(tunggu)

def _cari_berita(tanggal: date, query: str | None = None) -> list[BeritaMediaCloud]: # cari berita di Media Cloud
    """Ambil daftar berita (url, judul, sumber, tanggal) dari Media Cloud.
    Cepat -- belum ada download artikel maupun inferensi model.
    query dari Laravel (pengaturan keyword); kalau kosong pakai MEDIACLOUD_QUERY di .env."""
    query = query or settings.mediacloud_query
    if not settings.mediacloud_api_key:
        raise HTTPException(status_code=500, detail="MEDIACLOUD_API_KEY belum diisi di .env") # error key
    if not query:
        raise HTTPException(status_code=500, detail="Query kosong: kirim query dari Laravel atau isi MEDIACLOUD_QUERY di .env") # error query

    # Media Cloud API dan collection_id (Indonesian News)
    mc_search = mediacloud.api.SearchApi(settings.mediacloud_api_key)
    collection_ids = [settings.mediacloud_collection_id] if settings.mediacloud_collection_id else None

    # hasil scraping Media Cloud
    all_stories = []
    pagination_token = None
    more_stories = True
    try:
        while more_stories:
            page, pagination_token = _story_list(
                mc_search,
                query,
                start_date=tanggal,
                end_date=tanggal,
                collection_ids=collection_ids,
                pagination_token=pagination_token,
            )
            all_stories += page
            more_stories = pagination_token is not None
    except mediacloud.error.APIResponseError as e:
        # teruskan kode & pesan asli Media Cloud (misal 429 rate limited) supaya terbaca di log Laravel
        status = e.response.status_code
        logger.error("Media Cloud error %s untuk %s: %s", status, tanggal, e.data.get("note"))
        raise HTTPException(
            status_code=429 if status == 429 else 502,
            detail=f"Media Cloud HTTP {status}: {e.data.get('note') or 'error'}",
        )
    except requests.RequestException as e:
        logger.error("Gagal menghubungi Media Cloud untuk %s: %s", tanggal, e)
        raise HTTPException(status_code=502, detail=f"Gagal menghubungi Media Cloud: {e}")

    logger.info(f"Media Cloud  {tanggal} → {len(all_stories)} berita | query: {query}")

    stories: list[BeritaMediaCloud] = []
    for story in all_stories:
        url = story.get("url") or story.get("original_url")
        if not url:
            logger.warning("Berita dilewati karena URL kosong: %s", story.get("title", "(tanpa judul)"))
            continue

        publish_date = story.get("publish_date") or f"{tanggal}"
        stories.append(BeritaMediaCloud(
            url=url,
            judul=story.get("title") or "(tanpa judul)",
            sumber=story.get("media_name"),
            tanggal_berita=str(publish_date)[:10],
        ))

    return stories

def _proses_berita(story: BeritaMediaCloud) -> ScrapedArticle | None: # proses 1 berita
    # ekstrak konten lengkap dengan newspaper3k
    konten = _extract_full_text(story.url)
    if konten is None:
        return None

    # klasifikasi wilayah
    wilayah_result = _classify(konten, models.wilayah_tokenizer, models.wilayah_model)

    indikator_label = indikator_conf = None
    ringkasan: dict[str, str | None] = {}
    errors: list[str] = []
    status = "classified"

    # klasifikasi indikator + ringkasan 3 model untuk berita dengan label wilayah DKI
    if wilayah_result.label == "DKI":
        try:
            indikator_result = _classify(konten, models.indikator_tokenizer, models.indikator_model) # klasifikasi indikator
            indikator_label = indikator_result.label
            indikator_conf = indikator_result.confidence
        except Exception as e:
            logger.error(f"Gagal klasifikasi indikator untuk {story.url}: {e}")
            errors.append(f"indikator: {str(e)[:200]}")

        # ringkasan SDS, model yang gagal tidak menghentikan model lain
        ringkasan, errors_ringkasan = _ringkas_semua_model(konten)
        errors += errors_ringkasan
        status = "failed" if errors else "summarized"

    return ScrapedArticle(
        judul=story.judul,
        konten=konten,
        url=story.url,
        sumber=story.sumber,
        tanggal_berita=story.tanggal_berita,
        wilayah_label=wilayah_result.label,
        wilayah_confidence=wilayah_result.confidence,
        indikator_label=indikator_label,
        indikator_confidence=indikator_conf,
        ringkasan_indobart=ringkasan.get("indobart"),
        ringkasan_idt5=ringkasan.get("idt5"),
        ringkasan_gemini=ringkasan.get("gemini"),
        status=status,
        pesan_error="; ".join(errors) or None,
    )

@app.post("/pipeline/cari-berita", response_model=list[BeritaMediaCloud]) # scraping harian tahap 1
def cari_berita(req: CariBeritaRequest):
    # ambil daftar berita dari Media Cloud
    return _cari_berita(req.tanggal, req.query)

@app.post("/pipeline/proses-berita", response_model=ProsesBeritaResponse) # scraping harian tahap 2
def proses_berita(story: BeritaMediaCloud):
    # proses ekstrak berita, klasifikasi, SDS 3 model; hasilnya disimpan ke DB oleh Laravel
    # dipanggil oleh Laravel untuk tiap berita hasil /pipeline/cari-berita
    mulai = time.perf_counter()
    hasil = _proses_berita(story)

    # 1 baris ringkas per berita: status · wilayah · indikator · lama proses · situs · judul
    detik = f"{time.perf_counter() - mulai:.1f} dtk"
    situs = urlparse(story.url).netloc.removeprefix("www.")
    judul = story.judul if len(story.judul) <= 70 else story.judul[:67] + "..."
    if hasil is None:
        logger.info("Berita  gagal_ekstrak · %s · %s · %s", detik, situs, judul)
        return ProsesBeritaResponse(status="gagal_ekstrak")

    logger.info("Berita  %s · %s · %s · %s · %s · %s",
                hasil.status, hasil.wilayah_label, hasil.indikator_label or "-", detik, situs, judul)
    return ProsesBeritaResponse(status=hasil.status, berita=hasil)

# tes satu input teks manual
@app.post("/process/article", response_model=ProcessArticleResponse)
def process_article(req: ProcessArticleRequest):
    wilayah_result = _classify(req.text, models.wilayah_tokenizer, models.wilayah_model)

    if wilayah_result.label == "NON_DKI":
        return ProcessArticleResponse(wilayah=wilayah_result)

    indikator_result = _classify(req.text, models.indikator_tokenizer, models.indikator_model)
    ringkasan, errors = _ringkas_semua_model(req.text)

    return ProcessArticleResponse(
        wilayah=wilayah_result, indikator=indikator_result,
        ringkasan=ringkasan, pesan_error="; ".join(errors) or None,
    )
