<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordScraping extends Model
{
    protected $table = 'keyword_scraping';

    protected $fillable = ['keyword'];

    // query default
    public const KEYWORD_DASAR = ['BPS', 'Badan Pusat Statistik'];
    public const WILAYAH = 'DKI Jakarta';

    /**
     *   Query Default  : (BPS OR "Badan Pusat Statistik") AND "DKI Jakarta"
     *   + Inflasi      : ((BPS OR "Badan Pusat Statistik") OR Inflasi) AND "DKI Jakarta"  
     */
    public static function queryMediaCloud(?iterable $keywords = null): string
    {
        $keywords ??= static::orderBy('id')->pluck('keyword');

        $dasar = '(' . implode(' OR ', array_map([self::class, 'formatKeyword'], self::KEYWORD_DASAR)) . ')';

        $tambahan = collect($keywords)->map(fn($k) => self::formatKeyword($k))->all();
        if ($tambahan) {
            $dasar = '(' . implode(' OR ', [$dasar, ...$tambahan]) . ')';
        }

        return $dasar . ' AND ' . self::formatKeyword(self::WILAYAH);
    }

    // kata biasa ditulis biasa, frasa/kata dengan tanda baca/operator diberi kutip ("Ekspor Impor")
    public static function formatKeyword(string $keyword): string
    {
        $kataBiasa = preg_match('/^[\p{L}\p{N}]+$/u', $keyword)
            && ! in_array(strtoupper($keyword), ['AND', 'OR', 'NOT'], true);

        return $kataBiasa ? $keyword : '"' . $keyword . '"';
    }

    // buang karakter yang punya arti khusus di query Media Cloud (tanda kutip, kurung, wildcard, dll.)
    public static function bersihkan(string $keyword): string
    {
        $keyword = preg_replace('/["()\[\]{}:*?\\\\^~!+|&]/u', ' ', $keyword);

        return trim(preg_replace('/\s+/u', ' ', $keyword));
    }

    // keyword yang sudah termasuk query (dasar, wilayah, atau sudah ditambahkan)
    public static function sudahAda(string $keyword): bool
    {
        $kecil = mb_strtolower($keyword);
        $bawaan = array_map('mb_strtolower', [...self::KEYWORD_DASAR, self::WILAYAH]);

        return in_array($kecil, $bawaan, true)
            || static::whereRaw('LOWER(keyword) = ?', [$kecil])->exists();
    }
}
