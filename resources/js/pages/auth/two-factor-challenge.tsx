import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Shield, Key } from 'lucide-react';

export default function TwoFactorChallenge() {
    const [recovery, setRecovery] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        recovery_code: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('two-factor.login'), {
            onFinish: () => reset('code', 'recovery_code'),
        });
    };

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <Head title="Verificación de Dos Factores" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <Card className="border-0 shadow-none">
                    <CardHeader className="text-center">
                        <div className="mx-auto w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                            <Shield className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <CardTitle>Verificación de Seguridad</CardTitle>
                        <CardDescription>
                            Confirma el acceso a tu cuenta con tu aplicación de autenticación o un código de recuperación
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="w-full">
                            {/* Toggle buttons */}
                            <div className="flex rounded-lg bg-muted p-1 mb-6">
                                <button
                                    type="button"
                                    onClick={() => setRecovery(false)}
                                    className={`flex-1 flex items-center justify-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                        !recovery
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <Shield className="h-3 w-3 mr-1" />
                                    Aplicación
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setRecovery(true)}
                                    className={`flex-1 flex items-center justify-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                        recovery
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <Key className="h-3 w-3 mr-1" />
                                    Recuperación
                                </button>
                            </div>

                            <form onSubmit={submit}>
                                {!recovery ? (
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="code">Código de Autenticación</Label>
                                            <Input
                                                id="code"
                                                type="text"
                                                placeholder="123456"
                                                value={data.code}
                                                onChange={(e) => setData('code', e.target.value)}
                                                maxLength={6}
                                                className="text-center text-lg tracking-widest"
                                                autoFocus
                                                autoComplete="one-time-code"
                                            />
                                            <InputError message={errors.code} className="mt-2" />
                                            <p className="text-sm text-muted-foreground mt-2">
                                                Ingresa el código de 6 dígitos de tu aplicación de autenticación
                                            </p>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="recovery_code">Código de Recuperación</Label>
                                            <Input
                                                id="recovery_code"
                                                type="text"
                                                placeholder="ABCD-EFGH"
                                                value={data.recovery_code}
                                                onChange={(e) => setData('recovery_code', e.target.value.toUpperCase())}
                                                className="text-center text-lg tracking-widest font-mono"
                                                autoComplete="one-time-code"
                                            />
                                            <InputError message={errors.recovery_code} className="mt-2" />
                                            <p className="text-sm text-muted-foreground mt-2">
                                                Ingresa uno de tus códigos de recuperación de 8 caracteres
                                            </p>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center justify-end mt-6">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full"
                                    >
                                        {processing ? 'Verificando...' : 'Verificar'}
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <div className="mt-6 text-center">
                            <p className="text-sm text-muted-foreground">
                                ¿Problemas para acceder?{' '}
                                <button
                                    type="button"
                                    onClick={() => setRecovery(!recovery)}
                                    className="text-blue-600 hover:text-blue-500 underline"
                                >
                                    {recovery ? 'Usar aplicación de autenticación' : 'Usar código de recuperación'}
                                </button>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
