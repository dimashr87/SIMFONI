<table>
    <thead>
        <tr>
            <th>Periode</th>
            <th>Sumber</th>
            <th>Cakupan</th>
            <th>Subjek</th>
            <th>Arah</th>
            <th>Jenis Periode</th>
            <th>Link Bukti</th>
            <th>Tanggal Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fenomena as $f)
        <tr>
            <td>{{ $f->periode }}</td>
            <td>{{ $f->sumber }}</td>
            <td>{{ $f->cakupan }}</td>
            <td>{{ $f->subjek }}</td>
            <td>{{ $f->arah }}</td>
            <td>{{ $f->periodicity }}</td>
            <td>{{ $f->linkbukti }}</td>
            <td>{{ $f->tanggalcreated }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
