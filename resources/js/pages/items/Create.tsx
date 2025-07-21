import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, Save, ArrowLeft, Info } from 'lucide-react';

interface FormData {
    name: string;
    qr_code: string;
    description: string;
    price: string;
    unit: string;
    status: boolean;
}

export default function Create() {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        name: '',
        qr_code: '',
        description: '',
        price: '',
        unit: '',
        status: true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/items');
    };

    const breadcrumbs = [
        { name: 'Panel de Control', href: '/dashboard' },
        { name: 'Items', href: '/items' },
        { name: 'Crear Item', href: '/items/create', current: true },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Crear Item" />

            <div className="p-6 space-y-6">
                {/* Header */}
 
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Crear nuevo artículo
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Completa la información para crear un nuevo artículo
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
                        <CardTitle>Información del artículo</CardTitle>
                        <p className="text-sm text-gray-600">
                            Los campos marcados con <span className="text-red-500">*</span> son obligatorios
                        </p>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-8">
                            {/* Nombre */}
                            <div className="space-y-2">
                                <Label htmlFor="name">
                                    Nombre <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ingresa el nombre del item"
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && (
                                    <div className="flex items-center gap-2 text-red-600 text-sm">
                                        <AlertCircle className="h-4 w-4" />
                                        {errors.name}
                                    </div>
                                )}
                                <p className="text-sm text-gray-500">
                                    Nombre descriptivo del item (mínimo 2 caracteres)
                                </p>
                            </div>

                            {/* Código de barra */}
                            <div className="space-y-2">
                                <Label htmlFor="qr_code">Código de barra</Label>
                                <Input
                                    id="qr_code"
                                    type="text"
                                    value={data.qr_code}
                                    onChange={(e) => setData('qr_code', e.target.value)}
                                    placeholder="Código de barra del item (opcional)"
                                    className={errors.qr_code ? 'border-red-500' : ''}
                                />
                                {errors.qr_code && (
                                    <div className="flex items-center gap-2 text-red-600 text-sm">
                                        <AlertCircle className="h-4 w-4" />
                                        {errors.qr_code}
                                    </div>
                                )}
                                <p className="text-sm text-gray-500">
                                    Código de barra único para identificación rápida (opcional)
                                </p>
                            </div>

                            {/* Descripción */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Descripción opcional del item"
                                    rows={4}
                                    className={errors.description ? 'border-red-500' : ''}
                                />
                                {errors.description && (
                                    <div className="flex items-center gap-2 text-red-600 text-sm">
                                        <AlertCircle className="h-4 w-4" />
                                        {errors.description}
                                    </div>
                                )}
                                <p className="text-sm text-gray-500">
                                    Descripción detallada del item (opcional, máximo 1000 caracteres)
                                </p>
                            </div>

                            {/* Precio y Unidad */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Precio */}
                                <div className="space-y-2">
                                    <Label htmlFor="price">Precio</Label>
                                    <Input
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.price}
                                        onChange={(e) => setData('price', e.target.value)}
                                        placeholder="0.00"
                                        className={errors.price ? 'border-red-500' : ''}
                                    />
                                    {errors.price && (
                                        <div className="flex items-center gap-2 text-red-600 text-sm">
                                            <AlertCircle className="h-4 w-4" />
                                            {errors.price}
                                        </div>
                                    )}
                                    <p className="text-sm text-gray-500">
                                        Precio del item (opcional)
                                    </p>
                                </div>

                                {/* Unidad */}
                                <div className="space-y-2">
                                    <Label htmlFor="unit">Unidad</Label>
                                    <Input
                                        id="unit"
                                        type="text"
                                        value={data.unit}
                                        onChange={(e) => setData('unit', e.target.value)}
                                        placeholder="ej: pcs, kg, m, litros"
                                        className={errors.unit ? 'border-red-500' : ''}
                                    />
                                    {errors.unit && (
                                        <div className="flex items-center gap-2 text-red-600 text-sm">
                                            <AlertCircle className="h-4 w-4" />
                                            {errors.unit}
                                        </div>
                                    )}
                                    <p className="text-sm text-gray-500">
                                        Unidad de medida (opcional, máximo 50 caracteres)
                                    </p>
                                </div>
                            </div>

                            {/* Estado */}
                            <div className="space-y-2">
                                <Label htmlFor="status">Estado</Label>
                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="status"
                                        checked={data.status}
                                        onCheckedChange={(checked) => setData('status', checked)}
                                    />
                                    <Label htmlFor="status" className="text-sm">
                                        {data.status ? 'Activo' : 'Inactivo'}
                                    </Label>
                                </div>
                                <p className="text-sm text-gray-500">
                                    Define si el item estará disponible para operaciones
                                </p>
                            </div>

                            {/* Código Automático Info */}
                            <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div className="flex items-start">
                                    <div className="flex-shrink-0">
                                        <Info className="h-5 w-5 text-blue-400" />
                                    </div>
                                    <div className="ml-3">
                                        <h3 className="text-sm font-medium text-blue-800">
                                            Código Automático
                                        </h3>
                                        <div className="mt-1 text-sm text-blue-700">
                                            El código del item se generará automáticamente con el formato IT-00000001
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Botones */}
                            <div className="flex justify-end space-x-4 pt-6 border-t">
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/items">Cancelar</Link>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Creando...' : 'Crear artículo'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
