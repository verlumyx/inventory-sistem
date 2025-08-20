import React, { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Printer, TestTube, Wifi, Usb, Cable, Network, CheckCircle, AlertCircle } from 'lucide-react';

interface PrinterSettings {
    id: number;
    name: string;
    enabled: boolean;
    type: string;
    port: string;
    printer_name?: string;
    timeout: number;
    network_host?: string;
    network_port: number;
    network_timeout: number;
    baud_rate: number;
    data_bits: number;
    stop_bits: number;
    parity: string;
    flow_control: string;
    paper_width: number;
    paper_margin: number;
    line_spacing: number;
    retry_enabled: boolean;
    retry_attempts: number;
    retry_delay: number;
    log_enabled: boolean;
    log_level: string;
    is_default: boolean;
}

interface Props {
    settings: PrinterSettings;
    availableTypes: Record<string, string>;
    parityOptions: Record<string, string>;
    flowControlOptions: Record<string, string>;
    logLevels: Record<string, string>;
}

export default function PrinterConfiguration({ 
    settings, 
    availableTypes, 
    parityOptions, 
    flowControlOptions, 
    logLevels 
}: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;
    const [isTesting, setIsTesting] = useState(false);

    const { data, setData, put, processing, errors, reset } = useForm({
        name: settings.name || 'Impresora Principal',
        enabled: settings.enabled || false,
        type: settings.type || 'cups',
        port: settings.port || '',
        printer_name: settings.printer_name || '',
        timeout: settings.timeout || 5,
        network_host: settings.network_host || '192.168.1.100',
        network_port: settings.network_port || 9100,
        network_timeout: settings.network_timeout || 10,
        baud_rate: settings.baud_rate || 9600,
        data_bits: settings.data_bits || 8,
        stop_bits: settings.stop_bits || 1,
        parity: settings.parity || 'none',
        flow_control: settings.flow_control || 'none',
        paper_width: settings.paper_width || 32,
        paper_margin: settings.paper_margin || 0,
        line_spacing: settings.line_spacing || 1,
        retry_enabled: settings.retry_enabled || true,
        retry_attempts: settings.retry_attempts || 3,
        retry_delay: settings.retry_delay || 1,
        log_enabled: settings.log_enabled || true,
        log_level: settings.log_level || 'info',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('settings.printer.update'));
    };

    const handleTestConnection = () => {
        if (isTesting) return;

        setIsTesting(true);

        // Usar router.post para la prueba
        router.post(route('settings.printer.test'), {}, {
            onSuccess: () => {
                setIsTesting(false);
            },
            onError: () => {
                setIsTesting(false);
            },
            onFinish: () => {
                setIsTesting(false);
            }
        });
    };

    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'cups':
            case 'macos':
                return <Printer className="h-4 w-4" />;
            case 'usb':
                return <Usb className="h-4 w-4" />;
            case 'serial':
                return <Cable className="h-4 w-4" />;
            case 'network':
                return <Network className="h-4 w-4" />;
            default:
                return <Printer className="h-4 w-4" />;
        }
    };

    return (
        <AppLayout>
            <Head title="Configuración de Impresora" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Configuración de Impresora</h1>
                        <p className="text-muted-foreground">
                            Configure su impresora térmica para imprimir facturas
                        </p>
                    </div>

                {flash?.success && (
                    <div className="flex items-center gap-2 rounded-md bg-green-50 p-4 text-green-800">
                        <CheckCircle className="h-4 w-4" />
                        {flash.success}
                    </div>
                )}

                {(pageErrors?.error || Object.keys(errors).length > 0) && (
                    <div className="flex items-center gap-2 rounded-md bg-red-50 p-4 text-red-800">
                        <AlertCircle className="h-4 w-4" />
                        {pageErrors?.error || 'Por favor, corrija los errores en el formulario.'}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Configuración General */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Printer className="h-5 w-5" />
                                Configuración General
                            </CardTitle>
                            <CardDescription>
                                Configuración básica de la impresora
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nombre de la Impresora</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Ej: Impresora Principal"
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="enabled"
                                        checked={data.enabled}
                                        onCheckedChange={(checked) => setData('enabled', checked)}
                                    />
                                    <Label htmlFor="enabled">Impresión Habilitada</Label>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="type">Tipo de Conexión</Label>
                                    <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                        <SelectTrigger>
                                            <SelectValue>
                                                <div className="flex items-center gap-2">
                                                    {getTypeIcon(data.type)}
                                                    {availableTypes[data.type] || data.type}
                                                </div>
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(availableTypes).map(([key, label]) => (
                                                <SelectItem key={key} value={key}>
                                                    <div className="flex items-center gap-2">
                                                        {getTypeIcon(key)}
                                                        {label}
                                                    </div>
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && <p className="text-sm text-red-600">{errors.type}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="timeout">Timeout (segundos)</Label>
                                    <Input
                                        id="timeout"
                                        type="number"
                                        min="1"
                                        max="60"
                                        value={data.timeout}
                                        onChange={(e) => setData('timeout', parseInt(e.target.value))}
                                    />
                                    {errors.timeout && <p className="text-sm text-red-600">{errors.timeout}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Configuración de Conexión */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                {getTypeIcon(data.type)}
                                Configuración de Conexión
                            </CardTitle>
                            <CardDescription>
                                Configuración específica para {availableTypes[data.type] || data.type}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* CUPS/macOS Configuration */}
                            {(data.type === 'cups' || data.type === 'macos') && (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="port">Nombre de la Impresora</Label>
                                        <Input
                                            id="port"
                                            value={data.port}
                                            onChange={(e) => setData('port', e.target.value)}
                                            placeholder="Ej: TECH_CLA58"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Use 'lpstat -p' para ver impresoras disponibles
                                        </p>
                                        {errors.port && <p className="text-sm text-red-600">{errors.port}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="printer_name">Nombre Alternativo (Opcional)</Label>
                                        <Input
                                            id="printer_name"
                                            value={data.printer_name}
                                            onChange={(e) => setData('printer_name', e.target.value)}
                                            placeholder="Opcional"
                                        />
                                    </div>
                                </div>
                            )}

                            {/* USB/Serial Configuration */}
                            {(data.type === 'usb' || data.type === 'serial') && (
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="port">Puerto</Label>
                                        <Input
                                            id="port"
                                            value={data.port}
                                            onChange={(e) => setData('port', e.target.value)}
                                            placeholder="Ej: /dev/usb/lp0, /dev/ttyUSB0, COM1"
                                        />
                                        {errors.port && <p className="text-sm text-red-600">{errors.port}</p>}
                                    </div>

                                    {data.type === 'serial' && (
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="baud_rate">Baud Rate</Label>
                                                <Select value={data.baud_rate.toString()} onValueChange={(value) => setData('baud_rate', parseInt(value))}>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="9600">9600</SelectItem>
                                                        <SelectItem value="19200">19200</SelectItem>
                                                        <SelectItem value="38400">38400</SelectItem>
                                                        <SelectItem value="57600">57600</SelectItem>
                                                        <SelectItem value="115200">115200</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="data_bits">Data Bits</Label>
                                                <Select value={data.data_bits.toString()} onValueChange={(value) => setData('data_bits', parseInt(value))}>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="7">7</SelectItem>
                                                        <SelectItem value="8">8</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="stop_bits">Stop Bits</Label>
                                                <Select value={data.stop_bits.toString()} onValueChange={(value) => setData('stop_bits', parseInt(value))}>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="1">1</SelectItem>
                                                        <SelectItem value="2">2</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="parity">Paridad</Label>
                                                <Select value={data.parity} onValueChange={(value) => setData('parity', value)}>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {Object.entries(parityOptions).map(([key, label]) => (
                                                            <SelectItem key={key} value={key}>{label}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Network Configuration */}
                            {data.type === 'network' && (
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="network_host">Dirección IP</Label>
                                        <Input
                                            id="network_host"
                                            value={data.network_host}
                                            onChange={(e) => setData('network_host', e.target.value)}
                                            placeholder="192.168.1.100"
                                        />
                                        {errors.network_host && <p className="text-sm text-red-600">{errors.network_host}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="network_port">Puerto</Label>
                                        <Input
                                            id="network_port"
                                            type="number"
                                            min="1"
                                            max="65535"
                                            value={data.network_port}
                                            onChange={(e) => setData('network_port', parseInt(e.target.value))}
                                        />
                                        {errors.network_port && <p className="text-sm text-red-600">{errors.network_port}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="network_timeout">Timeout Red (seg)</Label>
                                        <Input
                                            id="network_timeout"
                                            type="number"
                                            min="1"
                                            max="120"
                                            value={data.network_timeout}
                                            onChange={(e) => setData('network_timeout', parseInt(e.target.value))}
                                        />
                                        {errors.network_timeout && <p className="text-sm text-red-600">{errors.network_timeout}</p>}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Botones de Acción */}
                    <div className="flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleTestConnection}
                            disabled={isTesting || !data.enabled}
                            className="flex items-center gap-2"
                        >
                            <TestTube className="h-4 w-4" />
                            {isTesting ? 'Probando...' : 'Probar Conexión'}
                        </Button>

                        <div className="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => reset()}
                                disabled={processing}
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Guardando...' : 'Guardar Configuración'}
                            </Button>
                        </div>
                    </div>
                </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
