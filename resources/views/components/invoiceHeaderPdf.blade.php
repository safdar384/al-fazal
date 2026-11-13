@php
    $headerImgPath = public_path('images/al-fazal-header.jpg');
    $headerImgSrc = null;
    if (is_file($headerImgPath)) {
        $headerImgSrc = 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($headerImgPath));
    }
@endphp
@if($headerImgSrc)
    <div class="pdf-banner-wrap">
        <img src="{{ $headerImgSrc }}" alt="Al Fazal Traders" class="pdf-banner">
    </div>
@else
    <div class="pdf-header">
        <table>
            <tr>
                <td>
                    <div class="pdf-brand">Al Fazal Traders</div>
                    <div class="pdf-tag">Azam Market Sargodha</div>
                    <div class="pdf-tag">Contact: 0322-6010149</div>
                </td>
                <td style="width:90px; text-align:right;">
                    <div class="pdf-mark">AF<br><span style="font-size:8px;">Al Fazal</span></div>
                </td>
            </tr>
        </table>
    </div>
@endif
