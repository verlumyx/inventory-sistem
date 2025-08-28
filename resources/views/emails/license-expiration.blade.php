<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licencia Próxima a Vencer</title>
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
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .warning-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .days-remaining {
            background-color: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
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
        .action-box {
            background-color: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
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
        <div class="warning-icon">⚠️</div>
        <h1>Licencia Próxima a Vencer</h1>
        @if($company)
            <h2 style="color: #dc3545; margin: 10px 0;">{{ $company->name_company }}</h2>
            <p>El Sistema de Inventario requiere renovación de licencia</p>
        @else
            <p>El Sistema de Inventario requiere renovación de licencia</p>
        @endif
    </div>

    <div class="days-remaining">
        @if($daysRemaining == 1)
            ¡La licencia expira MAÑANA!
        @elseif($daysRemaining == 0)
            ¡La licencia expira HOY!
        @else
            {{ $daysRemaining }} días restantes
        @endif
    </div>

    <h3>Información de la Licencia</h3>
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
            <th>Fecha de Expiración:</th>
            <td>{{ $license->end_date->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <th>Días Restantes:</th>
            <td>
                @if($daysRemaining <= 1)
                    <strong style="color: #dc3545;">{{ $daysRemaining }} días</strong>
                @elseif($daysRemaining <= 7)
                    <strong style="color: #fd7e14;">{{ $daysRemaining }} días</strong>
                @else
                    {{ $daysRemaining }} días
                @endif
            </td>
        </tr>
        <tr>
            <th>ID de Máquina:</th>
            <td><code>{{ $license->machine_id }}</code></td>
        </tr>
        <tr>
            <th>Usuario:</th>
            <td>{{ $license->user_email ?? 'No especificado' }}</td>
        </tr>
        <tr>
            <th>Fecha de Activación:</th>
            <td>{{ $license->activated_at ? $license->activated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
        </tr>
    </table>

    <div class="action-box">
        <h3>🔧 Acciones Requeridas</h3>
        <ol>
            <li><strong>Contactar al cliente</strong> para informar sobre la próxima expiración</li>
            <li><strong>Generar nuevo código</strong> si el cliente desea renovar la licencia</li>
            <li><strong>Proporcionar el código</strong> al cliente para que active la nueva licencia</li>
        </ol>
        
        @if($daysRemaining <= 3)
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin-top: 15px;">
            <strong>⚠️ URGENTE:</strong> La licencia expira en {{ $daysRemaining }} días o menos. 
            Se recomienda contactar al cliente inmediatamente.
        </div>
        @endif
    </div>

    <h3>📋 Proceso de Renovación</h3>
    <p>Para renovar la licencia, el cliente debe:</p>
    <ol>
        <li>Acceder al sistema y hacer clic en "Generar Código de Renovación"</li>
        <li>Esperar a recibir el código de activación de los administradores</li>
        <li>Ingresar el código en el sistema para activar la nueva licencia</li>
    </ol>

    <div class="footer">
        <p>Este email fue generado automáticamente por el Sistema de Inventario.</p>
        <p>Fecha de verificación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>No responda a este email.</p>
    </div>
</body>
</html>
