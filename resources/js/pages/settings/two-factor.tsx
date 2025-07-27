import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Shield, ShieldCheck, Key, AlertTriangle, Copy, CheckCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Autenticación de 2 Factores',
        href: '/settings/two-factor',
    },
];

interface Props {
    two_factor_enabled: boolean;
    two_factor_confirmed: boolean;
    two_factor_setup?: {
        secret: string;
        qr_code_url: string;
    };
    recovery_codes?: string[];
}

export default function TwoFactor({ two_factor_enabled, two_factor_confirmed, two_factor_setup, recovery_codes }: Props) {
    const { flash, errors } = usePage().props as any;
    const [showRecoveryCodes, setShowRecoveryCodes] = useState(false);
    const [showCurrentCodes, setShowCurrentCodes] = useState(false);
    const [copiedCode, setCopiedCode] = useState<string | null>(null);

    const {
        data: enableData,
        setData: setEnableData,
        post: enablePost,
        processing: enableProcessing,
        reset: enableReset,
    } = useForm({});

    const {
        data: confirmData,
        setData: setConfirmData,
        post: confirmPost,
        processing: confirmProcessing,
        errors: confirmErrors,
        reset: confirmReset,
    } = useForm({
        code: '',
    });

    const {
        data: disableData,
        setData: setDisableData,
        delete: deleteRequest,
        processing: disableProcessing,
        errors: disableErrors,
        reset: disableReset,
    } = useForm({
        password: '',
    });

    const {
        data: recoveryData,
        setData: setRecoveryData,
        post: recoveryPost,
        processing: recoveryProcessing,
        errors: recoveryErrors,
        reset: recoveryReset,
    } = useForm({
        password: '',
    });

    const {
        data: showCodesData,
        setData: setShowCodesData,
        post: showCodesPost,
        processing: showCodesProcessing,
        errors: showCodesErrors,
        reset: showCodesReset,
    } = useForm({
        password: '',
    });

    const enableTwoFactor: FormEventHandler = (e) => {
        e.preventDefault();
        console.log('Enable 2FA button clicked');
        console.log('Route:', route('two-factor.store'));

        enablePost(route('two-factor.store'), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('2FA enable success');
                enableReset();
            },
            onError: (errors) => {
                console.log('2FA enable error:', errors);
            },
            onFinish: () => {
                console.log('2FA enable finished');
            }
        });
    };

    const confirmTwoFactor: FormEventHandler = (e) => {
        e.preventDefault();
        confirmPost(route('two-factor.confirm'), {
            preserveScroll: true,
            onSuccess: () => {
                confirmReset();
                setShowRecoveryCodes(true);
            },
        });
    };

    const disableTwoFactor: FormEventHandler = (e) => {
        e.preventDefault();
        deleteRequest(route('two-factor.destroy'), {
            preserveScroll: true,
            onSuccess: () => disableReset(),
        });
    };

    const generateRecoveryCodes: FormEventHandler = (e) => {
        e.preventDefault();
        recoveryPost(route('two-factor.recovery-codes'), {
            preserveScroll: true,
            onSuccess: () => {
                recoveryReset();
                setShowRecoveryCodes(true);
            },
        });
    };

    const showExistingRecoveryCodes: FormEventHandler = (e) => {
        e.preventDefault();
        showCodesPost(route('two-factor.show-recovery-codes'), {
            preserveScroll: true,
            onSuccess: () => {
                showCodesReset();
                setShowRecoveryCodes(true);
            },
        });
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        setCopiedCode(text);
        setTimeout(() => setCopiedCode(null), 2000);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Autenticación de 2 Factores" />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Autenticación de 2 Factores"
                        description="Agrega una capa adicional de seguridad a tu cuenta"
                    />

                    {/* Status Messages */}
                    {flash?.status === 'two-factor-setup' && (
                        <Alert>
                            <Shield className="h-4 w-4" />
                            <AlertDescription>
                                Escanea el código QR con tu aplicación de autenticación y confirma con un código.
                            </AlertDescription>
                        </Alert>
                    )}

                    {flash?.status === 'two-factor-confirmed' && (
                        <Alert>
                            <ShieldCheck className="h-4 w-4" />
                            <AlertDescription>
                                ¡Autenticación de dos factores habilitada exitosamente! Guarda tus códigos de recuperación.
                            </AlertDescription>
                        </Alert>
                    )}

                    {flash?.status === 'two-factor-disabled' && (
                        <Alert>
                            <AlertTriangle className="h-4 w-4" />
                            <AlertDescription>
                                Autenticación de dos factores deshabilitada.
                            </AlertDescription>
                        </Alert>
                    )}

                    {/* Current Status */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Estado Actual
                            </CardTitle>
                            <CardDescription>
                                Estado de la autenticación de dos factores en tu cuenta
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                {two_factor_enabled ? (
                                    <>
                                        <Badge variant="default" className="bg-green-100 text-green-800 border-green-200">
                                            <ShieldCheck className="h-3 w-3 mr-1" />
                                            Habilitada
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">
                                            Tu cuenta está protegida con autenticación de dos factores
                                        </span>
                                    </>
                                ) : (
                                    <>
                                        <Badge variant="secondary">
                                            <Shield className="h-3 w-3 mr-1" />
                                            Deshabilitada
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">
                                            Tu cuenta no tiene autenticación de dos factores
                                        </span>
                                    </>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {!two_factor_enabled ? (
                        <div className="space-y-6">
                            {/* Enable Two Factor */}
                            {!(flash?.two_factor_setup || two_factor_setup) && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Habilitar Autenticación de 2 Factores</CardTitle>
                                        <CardDescription>
                                            Protege tu cuenta con un código adicional de tu teléfono
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={enableTwoFactor}>
                                            <div className="space-y-4">
                                                <p className="text-sm text-muted-foreground">
                                                    Cuando la autenticación de dos factores esté habilitada, se te pedirá un token seguro y aleatorio durante la autenticación.
                                                    Puedes obtener este token desde la aplicación Google Authenticator de tu teléfono.
                                                </p>

                                                <Button type="submit" disabled={enableProcessing}>
                                                    {enableProcessing ? 'Configurando...' : 'Habilitar'}
                                                </Button>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}

                            {/* QR Code Setup */}
                            {(flash?.two_factor_setup || two_factor_setup) && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Configurar Aplicación de Autenticación</CardTitle>
                                        <CardDescription>
                                            Escanea el código QR con Google Authenticator o ingresa la clave manualmente
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-4">
                                            <div className="flex flex-col items-center space-y-4">
                                                <div className="p-4 bg-white rounded-lg border">
                                                    <img
                                                        src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent((flash?.two_factor_setup || two_factor_setup)?.qr_code_url || '')}`}
                                                        alt="QR Code para 2FA"
                                                        className="w-48 h-48"
                                                    />
                                                </div>

                                                <div className="text-center">
                                                    <p className="text-sm text-muted-foreground mb-2">
                                                        O ingresa esta clave manualmente:
                                                    </p>
                                                    <div className="flex items-center gap-2 p-2 bg-muted rounded font-mono text-sm">
                                                        <span>{(flash?.two_factor_setup || two_factor_setup)?.secret}</span>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => copyToClipboard((flash?.two_factor_setup || two_factor_setup)?.secret || '')}
                                                        >
                                                            {copiedCode === (flash?.two_factor_setup || two_factor_setup)?.secret ? (
                                                                <CheckCircle className="h-4 w-4" />
                                                            ) : (
                                                                <Copy className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>

                                            <form onSubmit={confirmTwoFactor}>
                                                <div className="space-y-4">
                                                    <div>
                                                        <Label htmlFor="code">Código de Verificación</Label>
                                                        <Input
                                                            id="code"
                                                            type="text"
                                                            placeholder="123456"
                                                            value={confirmData.code}
                                                            onChange={(e) => setConfirmData('code', e.target.value)}
                                                            maxLength={6}
                                                            required
                                                        />
                                                        <InputError message={confirmErrors.code} className="mt-2" />
                                                        <p className="text-sm text-muted-foreground mt-1">
                                                            Ingresa el código de 6 dígitos de tu aplicación de autenticación
                                                        </p>
                                                    </div>

                                                    <Button type="submit" disabled={confirmProcessing}>
                                                        {confirmProcessing ? 'Verificando...' : 'Confirmar'}
                                                    </Button>
                                                </div>
                                            </form>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {/* Disable Two Factor */}
                            {two_factor_enabled && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="text-red-600">Deshabilitar Autenticación de 2 Factores</CardTitle>
                                        <CardDescription>
                                            Esto eliminará la protección adicional de tu cuenta
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={disableTwoFactor}>
                                            <div className="space-y-4">
                                                <div>
                                                    <Label htmlFor="disable_password">Contraseña Actual</Label>
                                                    <Input
                                                        id="disable_password"
                                                        type="password"
                                                        value={disableData.password}
                                                        onChange={(e) => setDisableData('password', e.target.value)}
                                                        required
                                                    />
                                                    <InputError message={disableErrors.password} className="mt-2" />
                                                </div>

                                                <Button type="submit" variant="destructive" disabled={disableProcessing}>
                                                    {disableProcessing ? 'Deshabilitando...' : 'Deshabilitar'}
                                                </Button>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    )}

                    {/* Recovery Codes Management - Always visible */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Key className="h-5 w-5" />
                                Gestionar Códigos de Recuperación
                            </CardTitle>
                            <CardDescription>
                                Ver códigos actuales o generar nuevos códigos de recuperación
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Mostrar códigos existentes */}
                                    <form onSubmit={showExistingRecoveryCodes}>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="show_codes_password">Ver Códigos Actuales</Label>
                                                <Input
                                                    id="show_codes_password"
                                                    type="password"
                                                    placeholder="Contraseña actual"
                                                    value={showCodesData.password}
                                                    onChange={(e) => setShowCodesData('password', e.target.value)}
                                                    required
                                                />
                                                <InputError message={showCodesErrors.password} className="mt-2" />
                                            </div>

                                            <Button type="submit" disabled={showCodesProcessing} className="w-full" variant="outline">
                                                {showCodesProcessing ? 'Mostrando...' : 'Ver Códigos'}
                                            </Button>
                                        </div>
                                    </form>

                                    {/* Generar nuevos códigos */}
                                    <form onSubmit={generateRecoveryCodes}>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="recovery_password">Generar Nuevos Códigos</Label>
                                                <Input
                                                    id="recovery_password"
                                                    type="password"
                                                    placeholder="Contraseña actual"
                                                    value={recoveryData.password}
                                                    onChange={(e) => setRecoveryData('password', e.target.value)}
                                                    required
                                                />
                                                <InputError message={recoveryErrors.password} className="mt-2" />
                                            </div>

                                            <Button type="submit" disabled={recoveryProcessing} className="w-full">
                                                {recoveryProcessing ? 'Generando...' : 'Generar Nuevos'}
                                            </Button>
                                        </div>
                                    </form>
                                </div>

                                <Alert>
                                    <AlertTriangle className="h-4 w-4" />
                                    <AlertDescription>
                                        <strong>Importante:</strong> Generar nuevos códigos invalidará todos los códigos anteriores.
                                    </AlertDescription>
                                </Alert>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Current Recovery Codes Display - Always visible */}
                    {recovery_codes && recovery_codes.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Key className="h-5 w-5" />
                                    Tus Códigos de Recuperación Actuales
                                </CardTitle>
                                <CardDescription>
                                    Estos son tus códigos de recuperación de emergencia. Guárdalos en un lugar seguro.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Alert className="mb-4">
                                    <AlertTriangle className="h-4 w-4" />
                                    <AlertDescription>
                                        <strong>Importante:</strong> Cada código solo se puede usar una vez. Guárdalos en un lugar seguro.
                                    </AlertDescription>
                                </Alert>

                                {!showCurrentCodes ? (
                                    <div className="text-center py-6">
                                        <div className="mb-4">
                                            <div className="text-lg font-mono text-muted-foreground">
                                                ••••-•••• ••••-•••• ••••-•••• ••••-••••
                                            </div>
                                            <div className="text-lg font-mono text-muted-foreground">
                                                ••••-•••• ••••-•••• ••••-•••• ••••-••••
                                            </div>
                                        </div>
                                        <Button
                                            type="button"
                                            onClick={() => setShowCurrentCodes(true)}
                                            className="gap-2"
                                        >
                                            <Key className="h-4 w-4" />
                                            Mostrar Códigos de Recuperación
                                        </Button>
                                    </div>
                                ) : (
                                    <>
                                        <div className="grid grid-cols-2 gap-2 p-4 bg-muted rounded-lg font-mono text-sm">
                                            {recovery_codes.map((code: string, index: number) => (
                                                <div key={index} className="flex items-center justify-between p-2 bg-background rounded border">
                                                    <span>{code}</span>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => copyToClipboard(code)}
                                                    >
                                                        {copiedCode === code ? (
                                                            <CheckCircle className="h-4 w-4" />
                                                        ) : (
                                                            <Copy className="h-4 w-4" />
                                                        )}
                                                    </Button>
                                                </div>
                                            ))}
                                        </div>

                                        <div className="mt-4 flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => {
                                                    const codes = recovery_codes.join('\n');
                                                    copyToClipboard(codes);
                                                }}
                                            >
                                                <Copy className="h-4 w-4 mr-2" />
                                                Copiar Todos
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setShowCurrentCodes(false)}
                                            >
                                                Ocultar Códigos
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* Recovery Codes Display */}
                    {(flash?.recovery_codes || (showRecoveryCodes && (flash?.status === 'recovery-codes-generated' || flash?.status === 'recovery-codes-shown' || flash?.status === 'two-factor-confirmed'))) && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Key className="h-5 w-5" />
                                    Códigos de Recuperación
                                </CardTitle>
                                <CardDescription>
                                    Guarda estos códigos en un lugar seguro. Cada código solo se puede usar una vez.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Alert className="mb-4">
                                    <AlertTriangle className="h-4 w-4" />
                                    <AlertDescription>
                                        <strong>Importante:</strong> Guarda estos códigos en un lugar seguro.
                                        Si pierdes el acceso a tu dispositivo de autenticación, estos códigos son la única forma de acceder a tu cuenta.
                                    </AlertDescription>
                                </Alert>

                                <div className="grid grid-cols-2 gap-2 p-4 bg-muted rounded-lg font-mono text-sm">
                                    {(flash?.recovery_codes || []).map((code: string, index: number) => (
                                        <div key={index} className="flex items-center justify-between p-2 bg-background rounded border">
                                            <span>{code}</span>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => copyToClipboard(code)}
                                            >
                                                {copiedCode === code ? (
                                                    <CheckCircle className="h-3 w-3" />
                                                ) : (
                                                    <Copy className="h-3 w-3" />
                                                )}
                                            </Button>
                                        </div>
                                    ))}
                                </div>

                                <div className="mt-4 flex gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => {
                                            const codes = (flash?.recovery_codes || []).join('\n');
                                            copyToClipboard(codes);
                                        }}
                                    >
                                        <Copy className="h-4 w-4 mr-2" />
                                        Copiar Todos
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setShowRecoveryCodes(false)}
                                    >
                                        Cerrar
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
