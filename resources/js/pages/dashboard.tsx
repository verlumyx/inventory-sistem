import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Package, Clock, CheckCircle, Warehouse, AlertTriangle, TrendingDown } from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Panel de Control',
        href: '/dashboard',
    },
];

interface DashboardStats {
    recent_entries: number;
    pending_invoices: number;
    paid_invoices: number;
}

interface ChartData {
    labels: string[];
    data: number[];
}

interface LowStockItem {
    id: number;
    code: string;
    name: string;
    price: number;
    unit: string;
    total_stock: number;
    stock_status: string;
}

interface DashboardProps {
    stats: DashboardStats;
    chart_data: ChartData;
    low_stock_items: LowStockItem[];
}

export default function Dashboard({ stats, chart_data, low_stock_items }: DashboardProps) {
    // Calcular el valor máximo para normalizar las barras
    const maxValue = Math.max(...chart_data.data);



    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Panel de Control" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Entradas Recientes</CardTitle>
                            <Package className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.recent_entries}</div>
                            <p className="text-xs text-muted-foreground">
                                Últimos 7 días
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Facturas Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-yellow-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">{stats.pending_invoices}</div>
                            <p className="text-xs text-muted-foreground">
                                Últimos 7 días
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Facturas Pagadas</CardTitle>
                            <CheckCircle className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{stats.paid_invoices}</div>
                            <p className="text-xs text-muted-foreground">
                                Últimos 7 días
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Items con Menor Disponibilidad */}
                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingDown className="h-5 w-5 text-red-600" />
                            Items con Menor Disponibilidad
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Los 10 artículos con menor stock disponible
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {low_stock_items.length > 0 ? (
                                low_stock_items.map((item, index) => (
                                    <div key={item.id} className="flex items-center justify-between p-3 rounded-lg border bg-card">
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center justify-center w-8 h-8 rounded-full bg-muted text-sm font-medium">
                                                {index + 1}
                                            </div>
                                            <div className="flex-1">
                                                <div className="font-medium text-sm">{item.name}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {item.code} • {formatCurrency(item.price)} • {item.unit}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className={`text-sm font-medium ${
                                                item.total_stock <= 0 ? 'text-red-600' :
                                                item.total_stock <= 5 ? 'text-yellow-600' : 'text-green-600'
                                            }`}>
                                                {item.total_stock} {item.unit}
                                            </div>
                                            <div className={`text-xs flex items-center gap-1 ${
                                                item.total_stock <= 0 ? 'text-red-600' :
                                                item.total_stock <= 5 ? 'text-yellow-600' : 'text-green-600'
                                            }`}>
                                                {item.total_stock <= 0 && <AlertTriangle className="h-3 w-3" />}
                                                {item.stock_status}
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    <Package className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                    <p>No hay items registrados</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
