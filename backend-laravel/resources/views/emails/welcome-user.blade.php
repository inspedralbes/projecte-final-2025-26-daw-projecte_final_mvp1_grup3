<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Benvinguda — {{ $nomApp }}</title>
    <!-- Fredoka: mateixa família que la web -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background-color:#f7faf5;-webkit-font-smoothing:antialiased;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f7faf5;background-image:linear-gradient(180deg,#f7faf5 0%,#eef4ea 100%);">
        <tr>
            <td align="center" style="padding:32px 16px 48px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:560px;margin:0 auto;">
                    <tr>
                        <td style="text-align:center;padding-bottom:24px;">
                            <span style="font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:32px;font-weight:800;letter-spacing:0.02em;color:#517d36;line-height:1.2;">{{ $nomApp }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 8px 30px rgba(58,88,38,0.08);border:1px solid rgba(81,125,54,0.12);">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#568039 0%,#486b2e 100%);padding:28px 28px 24px;">
                                        <p style="margin:0;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:20px;font-weight:700;color:#ffffff;line-height:1.35;">Hola, {{ $usuari->nom }}</p>
                                        <p style="margin:10px 0 0;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:15px;font-weight:500;color:rgba(255,255,255,0.92);line-height:1.5;">Benvingut/da a {{ $nomApp }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:28px 28px 8px;">
                                        <p style="margin:0 0 16px;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:16px;font-weight:500;color:#3a5826;line-height:1.6;">El teu compte ja està actiu. Pots començar a crear hàbits, guanyar XP i seguir el teu progrés des de l’app.</p>
                                        <p style="margin:0 0 24px;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:15px;font-weight:400;color:#5c6b52;line-height:1.55;">Ens alegra tenir-te amb nosaltres.</p>
                                        @if(!empty($appPublicUrl))
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;">
                                            <tr>
                                                <td align="center" style="border-radius:14px;background:#568039;box-shadow:0 4px 0 0 #3f5e29;">
                                                    <a href="{{ $appPublicUrl }}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:14px 28px;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;text-transform:uppercase;letter-spacing:0.06em;">Obrir {{ $nomApp }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 28px 28px;">
                                        <p style="margin:0;padding-top:16px;border-top:1px solid #e8efe4;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:13px;font-weight:500;color:#8a9a82;line-height:1.5;">— L’equip de {{ $nomApp }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;padding-top:20px;">
                            <p style="margin:0;font-family:'Fredoka',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:11px;color:#a8b5a0;line-height:1.5;">Aquest correu s’ha enviat automàticament quan has iniciat sessió per primer cop.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
