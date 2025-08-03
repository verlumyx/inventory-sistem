import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Shield } from 'lucide-react';

interface Props {
    email: string;
}

export default function PasswordResetTwoFactor({ email }: Props) {
    const [recovery, setRecovery] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        recovery_code: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.two-factor.verify'), {
            onFinish: () => reset('code', 'recovery_code'),
        });
    };

    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
            <Head title="Verificación de Seguridad - Recuperar Contraseña" />

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800">
                <Card className="border-0 shadow-none">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                            <Shield className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <CardTitle>Verificación de Seguridad</CardTitle>
                        <CardDescription>Para recuperar tu contraseña, confirma tu identidad con tu aplicación de autenticación</CardDescription>
                        <div className="text-muted-foreground mt-2 text-sm">
                            Cuenta: <span className="font-medium">{email}</span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="w-full">
                            <form onSubmit={submit}>
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="recovery_code">Código de Recuperación</Label>
                                        <Input
                                            id="recovery_code"
                                            type="text"
                                            placeholder="ABCD-EFGH"
                                            value={data.recovery_code}
                                            onChange={(e) => setData('recovery_code', e.target.value.toUpperCase())}
                                            className="text-center font-mono text-lg tracking-widest"
                                            autoComplete="one-time-code"
                                        />
                                        <InputError message={errors.recovery_code} className="mt-2" />
                                        <p className="text-muted-foreground mt-2 text-sm">
                                            Ingresa uno de tus códigos de recuperación (formato: XXXX-XXXX)
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-6 flex items-center justify-end">
                                    <Button type="submit" disabled={processing} className="w-full">
                                        {processing ? 'Verificando...' : 'Verificar y Continuar'}
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <div className="mt-6 text-center">
                            <p className="text-muted-foreground text-sm">
                                ¿Problemas para acceder?{' '}
                                <button type="button" onClick={() => setRecovery(!recovery)} className="text-blue-600 underline hover:text-blue-500">
                                    {recovery ? 'Usar aplicación de autenticación' : 'Usar código de recuperación'}
                                </button>
                            </p>
                        </div>

                        <div className="mt-4 text-center">
                            <a
                                href={route('password.request')}
                                className="text-muted-foreground hover:text-foreground inline-flex items-center text-sm"
                            >
                                <ArrowLeft className="mr-1 h-3 w-3" />
                                Volver a recuperación de contraseña
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
