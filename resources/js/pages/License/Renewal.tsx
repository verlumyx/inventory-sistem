import { Head, useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Clock, Key, LoaderCircle, Shield } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AuthLayout from '@/layouts/auth-layout';

interface License {
    id: number;
    license_code: string;
    start_date: string;
    end_date: string;
    status: string;
    machine_id: string;
    user_email?: string;
    activated_at?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

interface RenewalProps {
    currentLicense?: License;
    lastLicense?: License;
    machineId: string;
    hasActiveLicense: boolean;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
}

type ActivateForm = {
    license_code: string;
};

export default function Renewal({ currentLicense, lastLicense, machineId, hasActiveLicense, flash }: RenewalProps) {
    const { data, setData, post, processing, errors, reset } = useForm<ActivateForm>({
        license_code: '',
    });

    const { post: generatePost, processing: generating } = useForm();

    const submitActivation: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('license.activate'), {
            onSuccess: () => reset(),
        });
    };

    const generateCode = () => {
        generatePost(route('license.generate'));
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const getDaysRemaining = (endDate: string) => {
        const end = new Date(endDate);
        const now = new Date();
        const diffTime = end.getTime() - now.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return Math.max(0, diffDays);
    };

    return (
        <AuthLayout 
            title="Renovación de Licencia" 
            description="Gestione la licencia de su sistema de inventario"
        >
            <Head title="Renovación de Licencia" />

            <div className="space-y-6">
                {/* Flash Messages */}
                {flash?.success && (
                    <div className="rounded-md border border-green-200 bg-green-50 p-4 mb-6">
                        <div className="flex items-center">
                            <CheckCircle className="h-5 w-5 text-green-400 mr-3" />
                            <div>
                                <h3 className="text-sm font-medium text-green-800">Éxito</h3>
                                <p className="mt-1 text-sm text-green-700">{flash.success}</p>
                            </div>
                        </div>
                    </div>
                )}

                {flash?.error && (
                    <div className="rounded-md border border-red-200 bg-red-50 p-4 mb-6">
                        <div className="flex items-center">
                            <AlertCircle className="h-5 w-5 text-red-400 mr-3" />
                            <div>
                                <h3 className="text-sm font-medium text-red-800">Error</h3>
                                <p className="mt-1 text-sm text-red-700">{flash.error}</p>
                            </div>
                        </div>
                    </div>
                )}

                {flash?.warning && (
                    <div className="rounded-md border border-yellow-200 bg-yellow-50 p-4 mb-6">
                        <div className="flex items-center">
                            <AlertCircle className="h-5 w-5 text-yellow-400 mr-3" />
                            <div>
                                <h3 className="text-sm font-medium text-yellow-800">Advertencia</h3>
                                <p className="mt-1 text-sm text-yellow-700">{flash.warning}</p>
                            </div>
                        </div>
                    </div>
                )}

                {flash?.info && (
                    <div className="rounded-md border border-blue-200 bg-blue-50 p-4 mb-6">
                        <div className="flex items-center">
                            <AlertCircle className="h-5 w-5 text-blue-400 mr-3" />
                            <div>
                                <h3 className="text-sm font-medium text-blue-800">Información</h3>
                                <p className="mt-1 text-sm text-blue-700">{flash.info}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Estado actual de la licencia */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Shield className="h-5 w-5" />
                            Estado de la Licencia
                        </CardTitle>
                        <CardDescription>
                            Información sobre el estado actual de su licencia
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {hasActiveLicense && currentLicense ? (
                            <div className="space-y-4">
                                <Alert>
                                    <CheckCircle className="h-4 w-4" />
                                    <AlertTitle>Licencia Activa</AlertTitle>
                                    <AlertDescription>
                                        Su licencia está activa hasta el {formatDate(currentLicense.end_date)}
                                        ({getDaysRemaining(currentLicense.end_date)} días restantes)
                                    </AlertDescription>
                                </Alert>
                                
                                <div className="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <Label className="text-muted-foreground">Código de Licencia</Label>
                                        <p className="font-mono">{currentLicense.license_code}</p>
                                    </div>
                                    <div>
                                        <Label className="text-muted-foreground">Fecha de Activación</Label>
                                        <p>{currentLicense.activated_at ? formatDate(currentLicense.activated_at) : 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <Alert variant="destructive">
                                <AlertCircle className="h-4 w-4" />
                                <AlertTitle>Licencia Expirada o No Válida</AlertTitle>
                                <AlertDescription>
                                    Su licencia ha expirado o no es válida. Debe contactar al administrador del sistema 
                                    para obtener un nuevo código de activación.
                                </AlertDescription>
                            </Alert>
                        )}
                    </CardContent>
                </Card>

                {/* Generar nuevo código */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Key className="h-5 w-5" />
                            Solicitar Nueva Licencia
                        </CardTitle>
                        <CardDescription>
                            Genere un código de renovación para enviar a los administradores
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="text-sm text-muted-foreground">
                            <p>ID de Máquina: <code className="bg-muted px-1 py-0.5 rounded text-xs">{machineId}</code></p>
                        </div>
                        
                        <Button 
                            onClick={generateCode} 
                            disabled={generating}
                            className="w-full"
                        >
                            {generating ? (
                                <>
                                    <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                    Generando...
                                </>
                            ) : (
                                <>
                                    <Clock className="mr-2 h-4 w-4" />
                                    Generar Código de Renovación
                                </>
                            )}
                        </Button>
                        
                        <Alert>
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>
                                Al generar un código, se enviará automáticamente a los administradores del sistema 
                                por correo electrónico. Ellos le proporcionarán el código de activación.
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>

                <Separator />

                {/* Activar licencia */}
                <Card>
                    <CardHeader>
                        <CardTitle>Activar Licencia</CardTitle>
                        <CardDescription>
                            Ingrese el código de activación proporcionado por los administradores
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submitActivation} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="license_code">Código de Activación</Label>
                                <Input
                                    id="license_code"
                                    type="text"
                                    value={data.license_code}
                                    onChange={(e) => setData('license_code', e.target.value.toUpperCase())}
                                    placeholder="Ingrese el código de 10 caracteres"
                                    maxLength={10}
                                    className="font-mono"
                                    required
                                />
                                <InputError message={errors.license_code} />
                            </div>

                            <Button type="submit" disabled={processing} className="w-full">
                                {processing ? (
                                    <>
                                        <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                        Activando...
                                    </>
                                ) : (
                                    'Activar Licencia'
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Información adicional */}
                {lastLicense && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Última Licencia</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <Label className="text-muted-foreground">Código</Label>
                                    <p className="font-mono">{lastLicense.license_code}</p>
                                </div>
                                <div>
                                    <Label className="text-muted-foreground">Estado</Label>
                                    <p className="capitalize">{lastLicense.status}</p>
                                </div>
                                <div>
                                    <Label className="text-muted-foreground">Fecha de Expiración</Label>
                                    <p>{formatDate(lastLicense.end_date)}</p>
                                </div>
                                <div>
                                    <Label className="text-muted-foreground">Creada</Label>
                                    <p>{formatDate(lastLicense.created_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthLayout>
    );
}
