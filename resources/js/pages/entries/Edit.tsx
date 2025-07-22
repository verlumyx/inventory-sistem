import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Trash2, AlertCircle, Package, Lock, Eye } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Item {
    id: number;
    code: string;
    name: string;
    display_name: string;
    price?: number;
    unit?: string;
}

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
}

interface EntryItem {
    id?: number;
    item_id: number;
    warehouse_id: number;
    amount: number;
    item?: Item;
    warehouse?: Warehouse;
}

interface Entry {
    id: number;
    code: string;
    name: string;
    description?: string;
    status: number; // 0 = Por recibir, 1 = Recibido
    created_at: string;
    updated_at: string;
    items: EntryItem[];
}

interface FormData {
    name: string;
    description: string;
    status: number; // 0 = Por recibir, 1 = Recibido
    items: EntryItem[];
}

interface Props {
    entry: Entry;
    items: Item[];
    warehouses: Warehouse[];
}

export default function Edit({ entry, items, warehouses }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        name: entry.name,
        description: entry.description || '',
        status: entry.status,
        items: [],
    });

    // Inicializar items al cargar el componente
    useEffect(() => {
        const initialItems = entry.items.map(item => ({
            id: item.id,
            item_id: item.item_id,
            warehouse_id: item.warehouse_id,
            amount: item.amount,
            item: item.item,
            warehouse: item.warehouse,
        }));
        setData('items', initialItems);
    }, [entry.items]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        put(route('entries.update', entry.id));
    };

    // Estados para el formulario dinámico de items
    const [selectedItem, setSelectedItem] = useState('');
    const [selectedWarehouse, setSelectedWarehouse] = useState('');
    const [itemAmount, setItemAmount] = useState('');

    const addItem = () => {
        if (!selectedItem || !selectedWarehouse || !itemAmount) {
            return;
        }

        const itemId = parseInt(selectedItem);
        const warehouseId = parseInt(selectedWarehouse);
        const amount = parseFloat(itemAmount);

        // Verificar que el item no esté ya agregado en el mismo almacén
        if (data.items.some(item => item.item_id === itemId && item.warehouse_id === warehouseId)) {
            alert('Este item ya está agregado en este almacén');
            return;
        }

        const newItem: EntryItem = {
            item_id: itemId,
            warehouse_id: warehouseId,
            amount: amount,
        };

        setData('items', [...data.items, newItem]);

        // Limpiar formulario de item
        setSelectedItem('');
        setSelectedWarehouse('');
        setItemAmount('');
    };

    const removeItem = (index: number) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };



    const getSelectedItem = (itemId: number): Item | undefined => {
        return items.find(item => item.id === itemId);
    };

    const getSelectedWarehouse = (warehouseId: number): Warehouse | undefined => {
        return warehouses.find(warehouse => warehouse.id === warehouseId);
    };



    const getItemById = (id: number) => {
        return items.find(item => item.id === id);
    };

    const getWarehouseById = (id: number) => {
        return warehouses.find(warehouse => warehouse.id === id);
    };

    const canSubmit = () => {
        return data.name.trim() !== '' &&
               data.items.length > 0 &&
               data.items.every(item =>
                   item.item_id > 0 &&
                   item.warehouse_id > 0 &&
                   item.amount > 0
               );
    };

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Entradas', href: '/entries' },
        { title: entry.code, href: `/entries/${entry.id}` },
        { title: 'Editar', href: `/entries/${entry.id}/edit` },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar Entrada - ${entry.code}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">

                    <div className="flex-1">
                        <h1 className="text-2xl font-bold text-gray-900">Editar Entrada</h1>
                        <p className="text-gray-600 mt-1">
                            Modifica la entrada {entry.code} y sus items
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={`/entries/${entry.id}`}>
                            <Eye className="h-4 w-4 mr-2" />
                            Ver Detalle
                        </Link>
                    </Button>
                    <Link href="/entries">
                        <Button variant="outline">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Volver
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Información Básica */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Información Básica</CardTitle>
                            <CardDescription>
                                Datos generales de la entrada
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* Código (solo lectura) */}
                            <div className="space-y-2">
                                <Label htmlFor="code">Código</Label>
                                <div className="relative">
                                    <Input
                                        id="code"
                                        type="text"
                                        value={entry.code}
                                        disabled
                                        className="bg-gray-50"
                                    />
                                    <Lock className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                </div>
                                <p className="text-sm text-gray-500">
                                    El código de la entrada no se puede modificar
                                </p>
                            </div>

                            {/* Nombre */}
                            <div className="space-y-2">
                                <Label htmlFor="name">Nombre *</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Nombre de la entrada"
                                    className={errors.name ? 'border-red-500' : ''}
                                    required
                                />
                                {errors.name && (
                                    <div className="flex items-center gap-2 text-red-600 text-sm">
                                        <AlertCircle className="h-4 w-4" />
                                        {errors.name}
                                    </div>
                                )}
                                <p className="text-sm text-gray-500">
                                    Nombre descriptivo de la entrada (requerido, máximo 255 caracteres)
                                </p>
                            </div>

                            {/* Descripción */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Descripción</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Descripción opcional de la entrada"
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
                                    Descripción detallada de la entrada (opcional)
                                </p>
                            </div>



                            {/* Información de fechas */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <Label className="text-sm font-medium text-gray-500">Fecha de Creación</Label>
                                    <p className="text-sm text-gray-900">
                                        {new Date(entry.created_at).toLocaleString('es-ES')}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-sm font-medium text-gray-500">Última Modificación</Label>
                                    <p className="text-sm text-gray-900">
                                        {new Date(entry.updated_at).toLocaleString('es-ES')}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Agregar Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Plus className="h-5 w-5" />
                                Agregar Items
                            </CardTitle>
                            <CardDescription>
                                Agrega más items a la entrada de forma rápida y dinámica
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <Label htmlFor="item_select">Item</Label>
                                    <Select value={selectedItem} onValueChange={setSelectedItem}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar item" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {items.map((item) => (
                                                <SelectItem key={item.id} value={item.id.toString()}>
                                                    {item.display_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label htmlFor="warehouse_select">Almacén</Label>
                                    <Select value={selectedWarehouse} onValueChange={setSelectedWarehouse}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar almacén" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {warehouses.map((warehouse) => (
                                                <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                    {warehouse.display_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label htmlFor="amount">Cantidad</Label>
                                    <Input
                                        id="amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="0.00"
                                        value={itemAmount}
                                        onChange={(e) => setItemAmount(e.target.value)}
                                    />
                                </div>
                                <div className="flex items-end">
                                    <Button
                                        type="button"
                                        onClick={addItem}
                                        disabled={!selectedItem || !selectedWarehouse || !itemAmount}
                                        className="w-full"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Agregar
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Items de la Entrada */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Items de la Entrada ({data.items.length})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {items.length === 0 && (
                                <Alert>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>
                                        No hay items disponibles. Crea algunos items primero.
                                    </AlertDescription>
                                </Alert>
                            )}

                            {warehouses.length === 0 && (
                                <Alert>
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>
                                        No hay almacenes disponibles. Crea algunos almacenes primero.
                                    </AlertDescription>
                                </Alert>
                            )}

                            {data.items.length === 0 ? (
                                <div className="text-center py-8">
                                    <Package className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                    <p className="text-gray-500">No hay items agregados</p>
                                    <p className="text-sm text-gray-400">Agrega items usando el formulario de arriba</p>
                                </div>
                            ) : (
                                <div className="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Item</TableHead>
                                                <TableHead>Almacén</TableHead>
                                                <TableHead className="text-right">Cantidad</TableHead>
                                                <TableHead className="w-[100px]">Acciones</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {data.items.map((entryItem, index) => {
                                                const item = getItemById(entryItem.item_id) || entryItem.item;
                                                const warehouse = getWarehouseById(entryItem.warehouse_id) || entryItem.warehouse;
                                        
                                                return (
                                                    <TableRow key={index}>
                                                        <TableCell>
                                                            <div>
                                                                <div className="font-medium">{item?.name}</div>
                                                                <div className="text-sm text-gray-500">
                                                                    {item?.code} • {item?.unit}
                                                                </div>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div>
                                                                <div className="font-medium">{warehouse?.name}</div>
                                                                <div className="text-sm text-gray-500">
                                                                    {warehouse?.code}
                                                                </div>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            {Number(entryItem.amount).toFixed(2)}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => removeItem(index)}
                                                            >
                                                                <Trash2 className="h-4 w-4 text-red-500" />
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </div>
                            )}
                            {errors.items && (
                                <p className="text-sm text-red-600 mt-2">{errors.items}</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Botones de Acción */}
                    <div className="flex items-center justify-end space-x-4">
                        <Link href={route('entries.show', entry.id)}>
                            <Button type="button" variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            disabled={processing || !canSubmit()}
                        >
                            {processing ? 'Actualizando...' : 'Actualizar Entrada'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
