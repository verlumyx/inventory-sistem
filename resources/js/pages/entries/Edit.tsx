import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Trash2, AlertCircle, Package, Lock } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';

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

    const addItem = () => {
        const newItem: EntryItem = {
            item_id: 0,
            warehouse_id: 0,
            amount: 1,
        };
        setData('items', [...data.items, newItem]);
    };

    const removeItem = (index: number) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: keyof EntryItem, value: number) => {
        const newItems = [...data.items];
        newItems[index] = { ...newItems[index], [field]: value };
        setData('items', newItems);
    };

    const getSelectedItem = (itemId: number): Item | undefined => {
        return items.find(item => item.id === itemId);
    };

    const getSelectedWarehouse = (warehouseId: number): Warehouse | undefined => {
        return warehouses.find(warehouse => warehouse.id === warehouseId);
    };

    const isItemAlreadySelected = (itemId: number, currentIndex: number): boolean => {
        return data.items.some((item, index) =>
            item.item_id === itemId && index !== currentIndex
        );
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

    return (
        <AuthenticatedLayout>
            <Head title={`Editar Entrada - ${entry.code}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link href={route('entries.index')}>
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Volver
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Editar Entrada</h1>
                        <p className="text-muted-foreground">
                            Modifica la entrada {entry.code} y sus items
                        </p>
                    </div>
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

                    {/* Items de la Entrada */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Items de la Entrada</CardTitle>
                                    <CardDescription>
                                        Modifica los items que forman parte de esta entrada
                                    </CardDescription>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addItem}
                                    disabled={items.length === 0 || warehouses.length === 0}
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Agregar Item
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
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
                                <div className="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                                    <Package className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-4 text-lg font-semibold text-gray-900">No hay items</h3>
                                    <p className="mt-2 text-gray-500">
                                        Agrega items a esta entrada para comenzar
                                    </p>
                                    {items.length > 0 && warehouses.length > 0 && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="mt-4"
                                            onClick={addItem}
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Agregar Primer Item
                                        </Button>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {data.items.map((entryItem, index) => {
                                        const selectedItem = getSelectedItem(entryItem.item_id) || entryItem.item;
                                        const selectedWarehouse = getSelectedWarehouse(entryItem.warehouse_id) || entryItem.warehouse;
                                        
                                        return (
                                            <Card key={index} className="border-l-4 border-l-blue-500">
                                                <CardContent className="pt-1">
                                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                        {/* Item */}
                                                        <div className="space-y-2">
                                                            <Label>Item *</Label>
                                                            <Select
                                                                value={entryItem.item_id > 0 ? entryItem.item_id.toString() : ""}
                                                                onValueChange={(value) => updateItem(index, 'item_id', parseInt(value))}
                                                            >
                                                                <SelectTrigger>
                                                                    <SelectValue placeholder="Seleccionar item" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {items
                                                                        .filter(item => 
                                                                            item.id === entryItem.item_id || 
                                                                            !isItemAlreadySelected(item.id, index)
                                                                        )
                                                                        .map((item) => (
                                                                        <SelectItem key={item.id} value={item.id.toString()}>
                                                                            {item.display_name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                            {selectedItem && (
                                                                <p className="text-xs text-gray-500">
                                                                    {selectedItem.unit && `Unidad: ${selectedItem.unit}`}
                                                                    {selectedItem.price && ` • Precio: $${selectedItem.price}`}
                                                                </p>
                                                            )}
                                                        </div>

                                                        {/* Almacén */}
                                                        <div className="space-y-2">
                                                            <Label>Almacén *</Label>
                                                            <Select
                                                                value={entryItem.warehouse_id > 0 ? entryItem.warehouse_id.toString() : ""}
                                                                onValueChange={(value) => updateItem(index, 'warehouse_id', parseInt(value))}
                                                            >
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

                                                        {/* Cantidad */}
                                                        <div className="space-y-2">
                                                            <Label>Cantidad *</Label>
                                                            <Input
                                                                type="number"
                                                                min="0.01"
                                                                step="0.01"
                                                                value={entryItem.amount}
                                                                onChange={(e) => updateItem(index, 'amount', parseFloat(e.target.value) || 0)}
                                                                placeholder="0.00"
                                                            />
                                                            {selectedItem?.unit && (
                                                                <p className="text-xs text-gray-500">
                                                                    en {selectedItem.unit}
                                                                </p>
                                                            )}
                                                        </div>

                                                        {/* Acciones */}
                                                        <div className="flex items-end">
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => removeItem(index)}
                                                                className="text-red-600 hover:text-red-700"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            )}

                            {errors.items && (
                                <div className="flex items-center gap-2 text-red-600 text-sm">
                                    <AlertCircle className="h-4 w-4" />
                                    {errors.items}
                                </div>
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
