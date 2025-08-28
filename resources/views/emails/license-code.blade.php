<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Código de Licencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .code-box {
            background-color: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            letter-spacing: 2px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table th,
        .info-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Nuevo Código de Licencia Generado</h1>
        @if($company)
            <h2 style="color: #1976d2; margin: 10px 0;">{{ $company->name_company }}</h2>
            <p>Se ha generado un nuevo código de licencia para el Sistema de Inventario.</p>
        @else
            <p>Se ha generado un nuevo código de licencia para el Sistema de Inventario.</p>
        @endif
    </div>

    <div class="code-box">
        <h2>Código de Activación</h2>
        <div class="code">{{ $license->license_code }}</div>
        <p><small>Proporcione este código al cliente para activar su licencia</small></p>
    </div>

    <h3>Información de la Solicitud</h3>
    <table class="info-table">
        @if($company)
        <tr>
            <th>Empresa:</th>
            <td><strong>{{ $company->name_company }}</strong></td>
        </tr>
        <tr>
            <th>RIF/DNI:</th>
            <td>{{ $company->dni }}</td>
        </tr>
        <tr>
            <th>Dirección:</th>
            <td>{{ $company->address }}</td>
        </tr>
        <tr>
            <th>Teléfono:</th>
            <td>{{ $company->phone }}</td>
        </tr>
        @endif
        <tr>
            <th>Código de Licencia:</th>
            <td>{{ $license->license_code }}</td>
        </tr>
        <tr>
            <th>ID de Máquina:</th>
            <td><code>{{ $license->machine_id }}</code></td>
        </tr>
        <tr>
            <th>Email del Usuario:</th>
            <td>{{ $license->user_email ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <th>Fecha de Solicitud:</th>
            <td>{{ $license->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <th>Duración de la Licencia:</th>
            <td>6 meses</td>
        </tr>
        <tr>
            <th>Estado:</th>
            <td>{{ ucfirst($license->status) }}</td>
        </tr>
    </table>

    <div class="warning">
        <h4>⚠️ Instrucciones Importantes</h4>
        <ul>
            <li>Este código es válido únicamente para la máquina que lo solicitó</li>
            <li>El código debe ser proporcionado al cliente exactamente como aparece arriba</li>
            <li>Una vez activado, la licencia será válida por 6 meses</li>
            <li>El código solo puede ser usado una vez</li>
        </ul>
    </div>

    <h3>Código Encriptado (para referencia)</h3>
    <p style="font-family: monospace; font-size: 12px; word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
        {{ $encryptedCode }}
    </p>

    @if($license->notes)
    <h3>Notas Adicionales</h3>
    <p>{{ $license->notes }}</p>
    @endif

    <div class="footer">
        <p>Este email fue generado automáticamente por el Sistema de Inventario.</p>
        <p>Fecha de envío: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>No responda a este email.</p>
    </div>
</body>
</html>
