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
                        title="Códigos de Recuperación"
                        description="Agrega una capa adicional de seguridad a tu cuenta"
                    />

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
                                <div className="grid grid-cols-1 md:grid-cols-1 gap-4">
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
