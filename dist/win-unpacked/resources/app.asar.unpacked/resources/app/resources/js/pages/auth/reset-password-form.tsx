import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Lock, CheckCircle } from 'lucide-react';

interface Props {
    email: string;
}

export default function ResetPasswordForm({ email }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.two-factor.reset'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <Head title="Establecer Nueva Contraseña" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <Card className="border-0 shadow-none">
                    <CardHeader className="text-center">
                        <div className="mx-auto w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-4">
                            <CheckCircle className="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <CardTitle>Verificación Exitosa</CardTitle>
                        <CardDescription>
                            Tu identidad ha sido verificada. Ahora puedes establecer una nueva contraseña para tu cuenta.
                        </CardDescription>
                        <div className="mt-2 text-sm text-muted-foreground">
                            Cuenta: <span className="font-medium">{email}</span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit}>
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="password">Nueva Contraseña</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        autoFocus
                                        autoComplete="new-password"
                                    />
                                    <InputError message={errors.password} className="mt-2" />
                                </div>

                                <div>
                                    <Label htmlFor="password_confirmation">Confirmar Nueva Contraseña</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        required
                                        autoComplete="new-password"
                                    />
                                    <InputError message={errors.password_confirmation} className="mt-2" />
                                </div>

                                <div className="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div className="flex items-start">
                                        <Lock className="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" />
                                        <div className="text-sm text-blue-800 dark:text-blue-200">
                                            <p className="font-medium mb-1">Requisitos de contraseña:</p>
                                            <ul className="text-xs space-y-1">
                                                <li>• Mínimo 8 caracteres</li>
                                                <li>• Al menos una letra mayúscula</li>
                                                <li>• Al menos una letra minúscula</li>
                                                <li>• Al menos un número</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center justify-end mt-6">
                                    <Button 
                                        type="submit" 
                                        disabled={processing}
                                        className="w-full"
                                    >
                                        {processing ? 'Actualizando...' : 'Actualizar Contraseña'}
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
