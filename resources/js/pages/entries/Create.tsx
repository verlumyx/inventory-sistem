import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Trash2, AlertCircle, Package, Info } from 'lucide-react';
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
    item_id: number;
    warehouse_id: number;
    amount: number;
}

interface FormData {
    name: string;
    description: string;
    status: number; // 0 = Por recibir, 1 = Recibido
    items: EntryItem[];
}

interface Props {
    items: Item[];
    warehouses: Warehouse[];
}

export default function Create({ items, warehouses }: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        name: '',
        description: '',
        status: 0, // 0 = Por recibir (estado inicial)
        items: [],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        post(route('entries.store'));
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

    // Funciones helper para la tabla
    const getItemById = (itemId: number): Item | undefined => {
        return items.find(item => item.id === itemId);
    };

    const getWarehouseById = (warehouseId: number): Warehouse | undefined => {
        return warehouses.find(warehouse => warehouse.id === warehouseId);
    };



    const canSubmit = () => {
        const hasValidItems = data.items.length > 0 &&
                             data.items.every(item =>
                                 item.item_id > 0 &&
                                 item.warehouse_id > 0 &&
                                 item.amount > 0
                             );

        return data.name.trim() !== '' && hasValidItems;
    };

    return (
        <AuthenticatedLayout>
            <Head title="Nueva Entrada" />

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
                        <h1 className="text-3xl font-bold tracking-tight">Nueva Entrada</h1>
                        <p className="text-muted-foreground">
                            Crea una nueva entrada de inventario con sus items
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
                                            El código del almacén se generará automáticamente con el formato ET-00000001
                                        </div>
                                    </div>
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
                                Agrega items a la entrada de forma rápida y dinámica
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
                                                const item = getItemById(entryItem.item_id);
                                                const warehouse = getWarehouseById(entryItem.warehouse_id);
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

                    {/* Validación adicional */}
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



                    {/* Botones de Acción */}
                    <div className="flex items-center justify-end space-x-4">
                        <Link href={route('entries.index')}>
                            <Button type="button" variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            disabled={processing || !canSubmit()}
                        >
                            {processing ? 'Creando...' : 'Crear Entrada'}
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
