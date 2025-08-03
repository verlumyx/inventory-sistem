import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, Save, DollarSign, CheckCircle, Info } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface ExchangeRate {
    id: number | null;
    rate: number;
    formatted_rate: string;
    created_at: string | null;
    updated_at: string | null;
}

interface Props {
    exchangeRate: ExchangeRate;
}

interface FormData {
    rate: string;
}

export default function ExchangeRateSettings({ exchangeRate }: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;
    const { data, setData, put, processing, errors } = useForm<FormData>({
        rate: exchangeRate.rate.toString(),
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/settings/exchange-rate');
    };

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Configuración', href: '/settings' },
        { title: 'Tasa de Cambio', href: '/settings/exchange-rate' },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Configuración - Tasa de Cambio" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Tasa de Cambio
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Configura la tasa de cambio oficial para conversiones de moneda
                        </p>
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center gap-2">
                        <CheckCircle className="h-4 w-4" />
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md flex items-center gap-2">
                        <AlertCircle className="h-4 w-4" />
                        {flash.error}
                    </div>
                )}

                {pageErrors?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md flex items-center gap-2">
                        <AlertCircle className="h-4 w-4" />
                        {pageErrors.error}
                    </div>
                )}

                {/* Configuration Form */}
                <Card className="max-w-2xl shadow-sm">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <DollarSign className="h-5 w-5" />
                            Configuración de Tasa de Cambio
                        </CardTitle>
                        <p className="text-sm text-gray-600">
                            Establece la tasa de cambio oficial que se utilizará en el sistema
                        </p>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            {/* Current Rate Display */}
                            {exchangeRate.id && (
                                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Info className="h-4 w-4 text-blue-600" />
                                        <span className="text-sm font-medium text-blue-900">
                                            Tasa Actual
                                        </span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900">
                                        {exchangeRate.formatted_rate}
                                    </div>
                                    {exchangeRate.updated_at && (
                                        <div className="text-xs text-blue-700 mt-1">
                                            Última actualización: {new Date(exchangeRate.updated_at).toLocaleString('es-ES')}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Rate Input */}
                            <div className="space-y-2">
                                <Label htmlFor="rate">
                                    Tasa de Cambio Oficial <span className="text-red-500">*</span>
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="rate"
                                        type="number"
                                        step="0.0001"
                                        min="0.0001"
                                        max="999999.9999"
                                        value={data.rate}
                                        onChange={(e) => setData('rate', e.target.value)}
                                        placeholder="1.0000"
                                        className={`pr-12 ${errors.rate ? 'border-red-500' : ''}`}
                                    />
                                    <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <DollarSign className="h-4 w-4 text-gray-400" />
                                    </div>
                                </div>
                                {errors.rate && (
                                    <div className="flex items-center gap-2 text-red-600 text-sm">
                                        <AlertCircle className="h-4 w-4" />
                                        {errors.rate}
                                    </div>
                                )}
                                <p className="text-sm text-gray-500">
                                    Ingresa la tasa de cambio con hasta 4 decimales (ej: 1.2500)
                                </p>
                            </div>

                            {/* Submit Button */}
                            <div className="flex items-center justify-end pt-4">
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Guardando...' : 'Guardar Configuración'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Information Card */}
                <Card className="max-w-2xl bg-yellow-50 border-yellow-200">
                    <CardContent className="pt-6">
                        <div className="flex items-start space-x-3">
                            <Info className="h-5 w-5 text-yellow-600 mt-0.5" />
                            <div className="space-y-2">
                                <p className="text-sm font-medium text-yellow-900">
                                    Información importante sobre la tasa de cambio
                                </p>
                                <ul className="text-sm text-yellow-800 space-y-1">
                                    <li>• La tasa de cambio se utiliza para conversiones de moneda en el sistema</li>
                                    <li>• Debe ser un valor positivo mayor a 0</li>
                                    <li>• Puede tener hasta 4 decimales de precisión</li>
                                    <li>• Los cambios se aplicarán inmediatamente en todo el sistema</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
