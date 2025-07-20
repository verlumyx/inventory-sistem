import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, Save, ArrowLeft, Eye } from 'lucide-react';

interface Item {
    id: number;
    code: string;
    name: string;
    qr_code?: string;
    description?: string;
    price?: number;
    unit?: string;
    status: boolean;
    created_at: string;
    updated_at: string;
}

interface FormData {
    name: string;
    qr_code: string;
    description: string;
    price: string;
    unit: string;
    status: boolean;
}

interface Props {
    item: Item;
}

export default function Edit({ item }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        name: item.name,
        qr_code: item.qr_code || '',
        description: item.description || '',
        price: item.price ? item.price.toString() : '',
        unit: item.unit || '',
        status: item.status,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/items/${item.id}`);
    };

    const breadcrumbs = [
        { name: 'Panel de Control', href: '/dashboard' },
        { name: 'Items', href: '/items' },
        { name: item.name, href: `/items/${item.id}` },
        { name: 'Editar', href: `/items/${item.id}/edit`, current: true },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar ${item.name}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={`/items/${item.id}`}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Volver
                        </Link>
                    </Button>
                    <div className="flex-1">
                        <h1 className="text-2xl font-bold text-gray-900">Editar Item</h1>
                        <p className="text-gray-600 mt-1">
                            Modifica la información del artículo "{item.name}"
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/items/${item.id}`}>
                            <Eye className="h-4 w-4 mr-2" />
                            Ver artículo
                        </Link>
                    </Button>
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
                            {/* Código Display */}
                            <div className="space-y-2">
                                <Label>Código</Label>
                                <div className="px-3 py-2 bg-gray-50 border rounded-md">
                                    <span className="font-mono text-sm text-gray-700">
                                        {item.code}
                                    </span>
                                </div>
                                <p className="text-sm text-gray-500">
                                    El código del item no se puede modificar
                                </p>
                            </div>

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

                            {/* Información de fechas */}
                            <div className="bg-gray-50 rounded-lg p-4 space-y-2">
                                <h4 className="font-medium text-gray-900">Información del Sistema</h4>
                                <div className="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div>
                                        <span className="font-medium">Creado:</span>
                                        <br />
                                        {new Date(item.created_at).toLocaleString('es-ES')}
                                    </div>
                                    <div>
                                        <span className="font-medium">Última actualización:</span>
                                        <br />
                                        {new Date(item.updated_at).toLocaleString('es-ES')}
                                    </div>
                                </div>
                            </div>

                            {/* Botones */}
                            <div className="flex justify-end space-x-4 pt-6 border-t">
                                <Button type="button" variant="outline" asChild>
                                    <Link href={`/items/${item.id}`}>Cancelar</Link>
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Guardando...' : 'Guardar Cambios'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
