import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Save, Eye } from 'lucide-react';
import { BreadcrumbItem } from '@/types';
import InputError from '@/components/input-error';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    description: string | null;
    status: boolean;
    status_text: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    warehouse: Warehouse;
}

export default function Edit({ warehouse }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Almacenes',
            href: '/warehouses',
        },
        {
            title: warehouse.name,
            href: `/warehouses/${warehouse.id}`,
        },
        {
            title: 'Editar',
            href: `/warehouses/${warehouse.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors, isDirty } = useForm({
        name: warehouse.name,
        description: warehouse.description || '',
        status: warehouse.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('warehouses.update', warehouse.id));
    };

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar ${warehouse.name}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Editar Almacén
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Modifica la información del almacén {warehouse.code}
                        </p>
                    </div>
                    <div className="flex space-x-2">
                        <Link href={`/warehouses/${warehouse.id}`}>
                            <Button variant="outline">
                                <Eye className="h-4 w-4 mr-2" />
                                Ver Detalle
                            </Button>
                        </Link>
                        <Link href="/warehouses">
                            <Button variant="outline">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                    </div>
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
                            {/* Code Display */}
                            <div className="space-y-2">
                                <Label>Código</Label>
                                <div className="px-3 py-2 bg-gray-50 dark:bg-gray-800 border rounded-md">
                                    <span className="font-mono text-sm text-gray-700 dark:text-gray-300">
                                        {warehouse.code}
                                    </span>
                                </div>
                                <p className="text-xs text-gray-500">
                                    El código del almacén no se puede modificar
                                </p>
                            </div>

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

                            {/* Metadata */}
                            <div className="bg-gray-50 dark:bg-gray-800 rounded-md p-4 space-y-2">
                                <h4 className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Información del Sistema
                                </h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                    <div>
                                        <span className="font-medium">Creado:</span>{' '}
                                        {new Date(warehouse.created_at).toLocaleString('es-ES')}
                                    </div>
                                    <div>
                                        <span className="font-medium">Actualizado:</span>{' '}
                                        {new Date(warehouse.updated_at).toLocaleString('es-ES')}
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
                                <Button 
                                    type="submit" 
                                    disabled={processing || !isDirty}
                                >
                                    {processing ? (
                                        <>
                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                            Guardando...
                                        </>
                                    ) : (
                                        <>
                                            <Save className="h-4 w-4 mr-2" />
                                            Guardar Cambios
                                        </>
                                    )}
                                </Button>
                            </div>

                            {/* Dirty State Warning */}
                            {isDirty && (
                                <div className="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-md">
                                    <p className="text-sm">
                                        Tienes cambios sin guardar. Asegúrate de guardar antes de salir.
                                    </p>
                                </div>
                            )}
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
