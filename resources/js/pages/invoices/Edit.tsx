import React, { useState, useEffect } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Plus, Trash2, Receipt, Package, Calculator, Edit as EditIcon } from 'lucide-react';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
}

interface Item {
    id: number;
    code: string;
    name: string;
    price: number;
    unit: string;
    display_name: string;
}

interface InvoiceItem {
    id?: number;
    item_id: number;
    amount: number;
    price: number;
    subtotal?: number;
    item?: {
        id: number;
        code: string;
        name: string;
        unit: string;
        display_name: string;
    };
}

interface Invoice {
    id: number;
    code: string;
    warehouse_id: number;
    warehouse: Warehouse;
    items: InvoiceItem[];
    total_amount: number;
    items_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
    warehouses: Warehouse[];
    items: Item[];
}

export default function Edit({ invoice, warehouses, items }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        warehouse_id: invoice.warehouse_id.toString(),
        items: invoice.items.map(item => ({
            item_id: item.item_id,
            amount: item.amount,
            price: item.price,
        })) as InvoiceItem[],
    });

    const [selectedItem, setSelectedItem] = useState('');
    const [itemAmount, setItemAmount] = useState('');
    const [itemPrice, setItemPrice] = useState('');

    // Calcular total de la factura
    const calculateTotal = () => {
        return data.items.reduce((total, item) => total + (item.amount * item.price), 0);
    };

    // Agregar item a la factura
    const addItem = () => {
        if (!selectedItem || !itemAmount || !itemPrice) {
            return;
        }

        const itemId = parseInt(selectedItem);
        const amount = parseFloat(itemAmount);
        const price = parseFloat(itemPrice);

        // Verificar que el item no esté ya agregado
        if (data.items.some(item => item.item_id === itemId)) {
            alert('Este item ya está agregado a la factura');
            return;
        }

        const newItem: InvoiceItem = {
            item_id: itemId,
            amount: amount,
            price: price,
            subtotal: amount * price,
        };

        setData('items', [...data.items, newItem]);
        
        // Limpiar formulario de item
        setSelectedItem('');
        setItemAmount('');
        setItemPrice('');
    };

    // Remover item de la factura
    const removeItem = (index: number) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    // Actualizar precio cuando se selecciona un item
    const handleItemSelect = (itemId: string) => {
        setSelectedItem(itemId);
        const item = items.find(i => i.id === parseInt(itemId));
        if (item) {
            setItemPrice(item.price.toString());
        }
    };

    // Enviar formulario
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (data.items.length === 0) {
            alert('Debe agregar al menos un item a la factura');
            return;
        }

        put(route('invoices.update', invoice.id));
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const getItemById = (id: number) => {
        return items.find(item => item.id === id);
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => router.visit(route('invoices.show', invoice.id))}
                        >
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Volver
                        </Button>
                        <div>
                            <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                                Editar Factura {invoice.code}
                            </h2>
                            <p className="text-sm text-gray-600">
                                {invoice.warehouse.display_name}
                            </p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title={`Editar Factura ${invoice.code}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        
                        {/* Información General */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <EditIcon className="h-5 w-5" />
                                    Información General
                                </CardTitle>
                                <CardDescription>
                                    Modifica el almacén para la factura
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="warehouse_id">Almacén *</Label>
                                    <Select
                                        value={data.warehouse_id.toString()}
                                        onValueChange={(value) => setData('warehouse_id', value)}
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
                                    {errors.warehouse_id && (
                                        <p className="text-sm text-red-600 mt-1">{errors.warehouse_id}</p>
                                    )}
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
                                    Agrega más items a la factura de forma rápida y dinámica
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <Label htmlFor="item_select">Item</Label>
                                        <Select value={selectedItem} onValueChange={handleItemSelect}>
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
                                    <div>
                                        <Label htmlFor="price">Precio</Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            placeholder="0.00"
                                            value={itemPrice}
                                            onChange={(e) => setItemPrice(e.target.value)}
                                        />
                                    </div>
                                    <div className="flex items-end">
                                        <Button
                                            type="button"
                                            onClick={addItem}
                                            disabled={!selectedItem || !itemAmount || !itemPrice}
                                            className="w-full"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Agregar
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Lista de Items */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="h-5 w-5" />
                                    Items de la Factura ({data.items.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
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
                                                    <TableHead className="text-right">Cantidad</TableHead>
                                                    <TableHead className="text-right">Precio</TableHead>
                                                    <TableHead className="text-right">Subtotal</TableHead>
                                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {data.items.map((invoiceItem, index) => {
                                                    const item = getItemById(invoiceItem.item_id);
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
                                                            <TableCell className="text-right">
                                                                {Number(invoiceItem.amount).toFixed(2)}
                                                            </TableCell>
                                                            <TableCell className="text-right">
                                                                {formatCurrency(invoiceItem.price)}
                                                            </TableCell>
                                                            <TableCell className="text-right font-medium">
                                                                {formatCurrency(invoiceItem.amount * invoiceItem.price)}
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
                                                {/* Fila de Total */}
                                                <TableRow className="border-t-2 bg-gray-50">
                                                    <TableCell colSpan={3} className="text-right font-bold">
                                                        TOTAL:
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <span className="text-xl font-bold text-green-600">
                                                            {formatCurrency(calculateTotal())}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                                {errors.items && (
                                    <p className="text-sm text-red-600 mt-2">{errors.items}</p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Resumen y Acciones */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Calculator className="h-5 w-5" />
                                    Resumen
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex justify-between items-center mb-6">
                                    <div>
                                        <p className="text-sm text-gray-600">Total de Items: {data.items.length}</p>
                                        <p className="text-2xl font-bold text-green-600">
                                            Total: {formatCurrency(calculateTotal())}
                                        </p>
                                    </div>
                                </div>
                                
                                <div className="flex gap-4">
                                    <Button
                                        type="submit"
                                        disabled={processing || data.items.length === 0}
                                        className="flex-1"
                                    >
                                        <EditIcon className="mr-2 h-4 w-4" />
                                        {processing ? 'Actualizando...' : 'Actualizar Factura'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => router.visit(route('invoices.show', invoice.id))}
                                    >
                                        Cancelar
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
