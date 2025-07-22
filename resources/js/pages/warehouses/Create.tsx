import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Save } from 'lucide-react';
import { BreadcrumbItem } from '@/types';
import InputError from '@/components/input-error';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Almacenes', href: '/warehouses' },
    { title: 'Crear Almacén', href: '/warehouses/create' },
];

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
        status: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('warehouses.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear Almacén" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Crear Nuevo Almacén
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Completa la información para crear un nuevo almacén
                        </p>
                    </div>
                    <Link href="/warehouses">
                        <Button variant="outline">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Volver
                        </Button>
                    </Link>
                </div>

                {/* Form */}
                <Card className="max-w-2xl shadow-sm">
                    <CardHeader>
                        <CardTitle>Información del Almacén</CardTitle>
                        <CardDescription>
                            Los campos marcados con * son obligatorios
                        </CardDescription>
                    </CardHeader>
                    
                    <CardContent>
                        <form onSubmit={submit} className="space-y-8">
                            {/* Name Field */}
                            <div className="space-y-2">
                                <Label htmlFor="name">
                                    Nombre *
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ingresa el nombre del almacén"
                                    className={errors.name ? 'border-red-500' : ''}
                                    autoFocus
                                />
                                <InputError message={errors.name} />
                                <p className="text-xs text-gray-500">
                                    Nombre descriptivo del almacén (mínimo 2 caracteres)
                                </p>
                            </div>

                            {/* Description Field */}
                            <div className="space-y-2">
                                <Label htmlFor="description">
                                    Descripción
                                </Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Descripción opcional del almacén"
                                    className={errors.description ? 'border-red-500' : ''}
                                    rows={4}
                                />
                                <InputError message={errors.description} />
                                <p className="text-xs text-gray-500">
                                    Descripción detallada del almacén (opcional, máximo 1000 caracteres)
                                </p>
                            </div>

                            {/* Status Field */}
                            <div className="space-y-2">
                                <Label htmlFor="status">
                                    Estado
                                </Label>
                                <div className="flex items-center space-x-3">
                                    <Switch
                                        id="status"
                                        checked={data.status}
                                        onCheckedChange={(checked) => setData('status', checked)}
                                    />
                                    <span className="text-sm text-gray-600 dark:text-gray-400">
                                        {data.status ? 'Activo' : 'Inactivo'}
                                    </span>
                                </div>
                                <InputError message={errors.status} />
                                <p className="text-xs text-gray-500">
                                    Define si el almacén estará disponible para operaciones
                                </p>
                            </div>

                            {/* Code Info */}
                            <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                                <div className="flex items-start">
                                    <div className="flex-shrink-0">
                                        <svg className="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                        </svg>
                                    </div>
                                    <div className="ml-3">
                                        <h3 className="text-sm font-medium text-blue-800 dark:text-blue-200">
                                            Código Automático
                                        </h3>
                                        <div className="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                            El código del almacén se generará automáticamente con el formato WH-00000001
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* General Error */}
                            {errors.error && (
                                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                                    {errors.error}
                                </div>
                            )}

                            {/* Submit Buttons */}
                            <div className="flex items-center justify-end space-x-4 pt-6 border-t">
                                <Link href="/warehouses">
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                            Creando...
                                        </>
                                    ) : (
                                        <>
                                            <Save className="h-4 w-4 mr-2" />
                                            Crear Almacén
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
