import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Calculator, Edit as EditIcon, Eye, Package, Plus, Trash2, Check, X, RotateCcw } from 'lucide-react';
import ItemSearchSelect from '@/components/ItemSearchSelect';
import React, { useState, useRef } from 'react';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
    default?: boolean;
}

interface Item {
    id: number;
    code: string;
    name: string;
    price: number;
    unit?: string;
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
    status: number;
    status_text: string;
    is_pending: boolean;
    is_paid: boolean;
    can_edit: boolean;
    warehouse: Warehouse;
    items: InvoiceItem[];
    total_amount: number;
    items_count: number;
    rate: number;
    formatted_rate: string;
    should_show_rate: boolean;
    total_amount_bs: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
    warehouses: Warehouse[];
    items: Item[];
    defaultWarehouse?: Warehouse | null;
    currentRate: number;
    shouldShowRate: boolean;
}

export default function Edit({ invoice, warehouses, items, defaultWarehouse, currentRate, shouldShowRate }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        warehouse_id: invoice.warehouse_id.toString(),
        items: invoice.items.map((item) => ({
            item_id: item.item_id,
            amount: item.amount,
            price: item.price,
            subtotal: item.amount * item.price,
        })) as InvoiceItem[],
        rate: invoice.rate,
    });

    const [selectedItem, setSelectedItem] = useState('');
    const [itemAmount, setItemAmount] = useState('');
    const [itemPrice, setItemPrice] = useState('');
    const [editingIndex, setEditingIndex] = useState<number | null>(null);
    const [editingAmount, setEditingAmount] = useState('');

    // Referencias para manejar el focus
    const amountInputRef = useRef<HTMLInputElement>(null);
    const priceInputRef = useRef<HTMLInputElement>(null);

    // Calcular total de la factura
    const calculateTotal = () => {
        return data.items.reduce((total, item) => total + item.amount * item.price, 0);
    };

    // Calcular total en bolívares
    const calculateTotalBs = () => {
        return calculateTotal() * (data.rate || 1);
    };

    // Limpiar formulario de item
    const clearItemForm = () => {
        setSelectedItem('');
        setItemAmount('');
        setItemPrice('');
    };

    // Agregar item a la factura
    const addItem = () => {
        if (!selectedItem || !itemAmount || !itemPrice) {
            return;
        }

        const itemId = parseInt(selectedItem);
        const amount = parseFloat(itemAmount);
        const price = parseFloat(itemPrice);

        // Verificar si el item ya existe en la factura
        const existingItemIndex = data.items.findIndex((item) => item.item_id === itemId);

        if (existingItemIndex !== -1) {
            // Si el item ya existe, sumar la cantidad
            const updatedItems = [...data.items];
            const existingItem = updatedItems[existingItemIndex];
            const existingAmount = parseFloat(existingItem.amount.toString()); // Convertir a número
            const existingPrice = parseFloat(existingItem.price.toString()); // Convertir a número
            const newAmount = existingAmount + amount;

            updatedItems[existingItemIndex] = {
                ...existingItem,
                amount: newAmount,
                subtotal: newAmount * existingPrice, // Usar el precio del item existente
            };

            setData('items', updatedItems);
        } else {
            // Si el item no existe, agregarlo como nuevo
            const newItem: InvoiceItem = {
                item_id: itemId,
                amount: amount,
                price: price,
                subtotal: amount * price,
            };
            setData('items', [...data.items, newItem]);
        }

        // Limpiar formulario de item
        clearItemForm();

        // Enfocar de nuevo el selector de items para agregar más items rápidamente
        setTimeout(() => {
            // El ItemSearchSelect manejará el focus internamente cuando se limpie
        }, 100);
    };

    // Remover item de la factura
    const removeItem = (index: number) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    // Iniciar edición de cantidad
    const startEditingAmount = (index: number) => {
        setEditingIndex(index);
        setEditingAmount(data.items[index].amount.toString());

        // Seleccionar todo el texto después de que el input se renderice
        setTimeout(() => {
            const input = document.querySelector(`input[data-editing-index="${index}"]`) as HTMLInputElement;
            if (input) {
                input.select();
            }
        }, 50);
    };

    // Cancelar edición de cantidad
    const cancelEditingAmount = () => {
        setEditingIndex(null);
        setEditingAmount('');
    };

    // Guardar nueva cantidad
    const saveEditingAmount = () => {
        if (editingIndex === null || !editingAmount) return;

        const newAmount = parseFloat(editingAmount);
        if (newAmount <= 0) return;

        const updatedItems = [...data.items];
        const item = updatedItems[editingIndex];
        updatedItems[editingIndex] = {
            ...item,
            amount: newAmount,
            subtotal: newAmount * item.price,
        };

        setData('items', updatedItems);
        setEditingIndex(null);
        setEditingAmount('');
    };

    // Actualizar precio cuando se selecciona un item
    const handleItemSelect = (itemId: string) => {
        setSelectedItem(itemId);
        const item = items.find((i) => i.id === parseInt(itemId));
        if (item) {
            setItemPrice(item.price.toString());
        }

        // Enfocar el campo de cantidad después de seleccionar un item
        setTimeout(() => {
            amountInputRef.current?.focus();
        }, 100);
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
            currency: 'USD',
        }).format(amount);
    };

    const getItemById = (id: number) => {
        return items.find((item) => item.id === id);
    };

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Facturas', href: '/invoices' },
        { title: invoice.code, href: `/invoices/${invoice.id}` },
        { title: 'Editar', href: `/invoices/${invoice.id}/edit` },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Editar Factura ${invoice.code}`} />
            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <div className="flex-1">
                        <h1 className="text-2xl font-bold text-gray-900">Editar Factura</h1>
                        <p className="mt-1 text-gray-600">Modifica la factura {invoice.code} y sus items</p>
                    </div>
                    <Button asChild>
                        <Link href={`/invoices/${invoice.id}`}>
                            <Eye className="mr-2 h-4 w-4" />
                            Ver Detalle
                        </Link>
                    </Button>
                    <Link href="/invoices">
                        <Button variant="outline">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Volver
                        </Button>
                    </Link>
                </div>
            </div>
            <div className="pb-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Información General */}
                        <Card>
                            <CardHeader className="relative">
                                <CardTitle className="flex items-center gap-2">
                                    <EditIcon className="h-5 w-5" />
                                    Información General
                                </CardTitle>
                                <CardDescription>Modifica el almacén para la factura</CardDescription>

                                {/* Tasa de cambio en la esquina superior derecha */}
                                {shouldShowRate && (
                                    <div className="absolute top-2 right-2 text-center">
                                        <p className="text-xs font-medium text-blue-900">Tasa de Cambio</p>
                                        <p className="text-2xl font-bold text-blue-600">Bs {invoice.formatted_rate}</p>
                                    </div>
                                )}
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Resumen de la factura */}
                                    <div>
                                        <div className="mb-6 space-y-4">
                                            <div>
                                                <p className="text-sm text-gray-600">Total de Items: {data.items.length}</p>
                                                <p className="text-2xl font-bold text-green-600">Total: {formatCurrency(calculateTotal())}</p>
                                                {shouldShowRate && (
                                                    <p className="text-lg font-semibold text-blue-600">
                                                        Total: {new Intl.NumberFormat('es-VE', {
                                                        style: 'currency',
                                                        currency: 'VES',
                                                    }).format(calculateTotalBs())}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <Label htmlFor="warehouse_id">Almacén *</Label>
                                        <Select value={data.warehouse_id.toString()} onValueChange={(value) => setData('warehouse_id', value)}>
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
                                        {errors.warehouse_id && <p className="mt-1 text-sm text-red-600">{errors.warehouse_id}</p>}
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
                                <CardDescription>Agrega más items a la factura de forma rápida y dinámica</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                                    <div>
                                        <Label htmlFor="item_select">Item</Label>
                                        <ItemSearchSelect
                                            items={items}
                                            value={selectedItem}
                                            onValueChange={handleItemSelect}
                                            placeholder="Buscar item por nombre o código..."
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="amount">Cantidad</Label>
                                        <Input
                                            ref={amountInputRef}
                                            id="amount"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            placeholder="0.00"
                                            value={itemAmount}
                                            onChange={(e) => setItemAmount(e.target.value)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Tab') {
                                                    setTimeout(() => {
                                                        priceInputRef.current?.focus();
                                                    }, 50);
                                                } else if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    if (selectedItem && itemAmount && itemPrice) {
                                                        addItem();
                                                    }
                                                }
                                            }}
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="price">Precio</Label>
                                        <Input
                                            ref={priceInputRef}
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            placeholder="0.00"
                                            disabled={true}
                                            value={itemPrice}
                                            onChange={(e) => setItemPrice(e.target.value)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    if (selectedItem && itemAmount && itemPrice) {
                                                        addItem();
                                                    }
                                                }
                                            }}
                                        />
                                    </div>
                                    <div className="flex items-end gap-2">
                                        <Button
                                            type="button"
                                            onClick={addItem}
                                            disabled={!selectedItem || !itemAmount || !itemPrice}
                                            className="flex-1"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Agregar
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={clearItemForm}
                                            disabled={!selectedItem && !itemAmount && !itemPrice}
                                        >
                                            <RotateCcw className="mr-2 h-4 w-4" />
                                            Limpiar
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
                                    <div className="py-8 text-center">
                                        <Package className="mx-auto mb-4 h-12 w-12 text-gray-400" />
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
                                                                {editingIndex === index ? (
                                                                    <Input
                                                                        type="number"
                                                                        value={editingAmount}
                                                                        onChange={(e) => setEditingAmount(e.target.value)}
                                                                        className="w-20 text-right"
                                                                        step="0.01"
                                                                        min="0.01"
                                                                        data-editing-index={index}
                                                                        onKeyDown={(e) => {
                                                                            if (e.key === 'Enter') saveEditingAmount();
                                                                            if (e.key === 'Escape') cancelEditingAmount();
                                                                        }}
                                                                        autoFocus
                                                                    />
                                                                ) : (
                                                                    Number(invoiceItem.amount).toFixed(2)
                                                                )}
                                                            </TableCell>
                                                            <TableCell className="text-right">{formatCurrency(invoiceItem.price)}</TableCell>
                                                            <TableCell className="text-right font-medium">
                                                                {formatCurrency(invoiceItem.subtotal || (invoiceItem.amount * invoiceItem.price))}
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="flex gap-1">
                                                                    {editingIndex === index ? (
                                                                        <>
                                                                            <Button type="button" variant="ghost" size="sm" onClick={saveEditingAmount}>
                                                                                <Check className="h-4 w-4 text-green-500" />
                                                                            </Button>
                                                                            <Button type="button" variant="ghost" size="sm" onClick={cancelEditingAmount}>
                                                                                <X className="h-4 w-4 text-gray-500" />
                                                                            </Button>
                                                                        </>
                                                                    ) : (
                                                                        <>
                                                                            <Button type="button" variant="ghost" size="sm" onClick={() => startEditingAmount(index)}>
                                                                                <EditIcon className="h-4 w-4 text-blue-500" />
                                                                            </Button>
                                                                            <Button type="button" variant="ghost" size="sm" onClick={() => removeItem(index)}>
                                                                                <Trash2 className="h-4 w-4 text-red-500" />
                                                                            </Button>
                                                                        </>
                                                                    )}
                                                                </div>
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
                                                        <span className="text-xl font-bold text-green-600">{formatCurrency(calculateTotal())}</span>
                                                    </TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                                {errors.items && <p className="mt-2 text-sm text-red-600">{errors.items}</p>}
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
                                <div className="mb-6 space-y-4">
                                    <div>
                                        <p className="text-sm text-gray-600">Total de Items: {data.items.length}</p>
                                        <p className="text-2xl font-bold text-green-600">Total: {formatCurrency(calculateTotal())}</p>
                                        {shouldShowRate && (
                                            <p className="text-lg font-semibold text-blue-600">
                                                Total : {new Intl.NumberFormat('es-VE', {
                                                    style: 'currency',
                                                    currency: 'VES',
                                                }).format(calculateTotalBs())}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex gap-4">
                                    <Button type="submit" disabled={processing || data.items.length === 0} className="flex-1">
                                        <EditIcon className="mr-2 h-4 w-4" />
                                        {processing ? 'Actualizando...' : 'Actualizar Factura'}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={() => router.visit(route('invoices.show', invoice.id))}>
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
