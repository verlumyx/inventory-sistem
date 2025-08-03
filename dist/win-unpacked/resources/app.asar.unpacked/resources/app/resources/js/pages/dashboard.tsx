import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Package, Clock, CheckCircle, TrendingUp } from 'lucide-react';

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

interface DashboardProps {
    stats: DashboardStats;
    chart_data: ChartData;
}

export default function Dashboard({ stats, chart_data }: DashboardProps) {
    // Calcular el valor máximo para normalizar las barras
    const maxValue = Math.max(...chart_data.data);

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD'
        }).format(value);
    };

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

                {/* Chart */}
                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Montos de Facturas Pagadas por Mes
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {chart_data.labels.map((label, index) => {
                                const value = chart_data.data[index];
                                const percentage = maxValue > 0 ? (value / maxValue) * 100 : 0;

                                return (
                                    <div key={index} className="space-y-2">
                                        <div className="flex justify-between items-center text-sm">
                                            <span className="font-medium">{label}</span>
                                            <span className="text-green-600 font-semibold">
                                                {formatCurrency(value)}
                                            </span>
                                        </div>
                                        <div className="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                                            <div
                                                className="bg-green-500 h-3 rounded-full transition-all duration-300 ease-in-out"
                                                style={{ width: `${percentage}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
