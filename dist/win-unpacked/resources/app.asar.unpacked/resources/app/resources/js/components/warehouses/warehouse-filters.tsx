import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Search, Filter, X } from 'lucide-react';

interface Filters {
    search?: string;
    status?: boolean;
    name?: string;
    code?: string;
}

interface WarehouseFiltersProps {
    filters: Filters;
    onFiltersChange?: (filters: Filters) => void;
    showToggle?: boolean;
}

export default function WarehouseFilters({ 
    filters, 
    onFiltersChange,
    showToggle = true 
}: WarehouseFiltersProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState<string>(
        filters.status !== undefined ? (filters.status ? 'true' : 'false') : 'all'
    );
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = () => {
        const params: any = {};
        
        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter === 'true';
        
        if (onFiltersChange) {
            onFiltersChange(params);
        } else {
            router.get('/warehouses', params, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const clearFilters = () => {
        setSearchTerm('');
        setStatusFilter('all');
        
        if (onFiltersChange) {
            onFiltersChange({});
        } else {
            router.get('/warehouses', {}, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const hasActiveFilters = searchTerm || statusFilter !== 'all';

    return (
        <Card>
            <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <CardTitle className="text-lg">Filtros</CardTitle>
                        {hasActiveFilters && (
                            <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                Activos
                            </span>
                        )}
                    </div>
                    {showToggle && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setShowFilters(!showFilters)}
                        >
                            <Filter className="h-4 w-4 mr-2" />
                            {showFilters ? 'Ocultar' : 'Mostrar'}
                        </Button>
                    )}
                </div>
            </CardHeader>
            
            {(showFilters || !showToggle) && (
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label htmlFor="search">Buscar</Label>
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <Input
                                    id="search"
                                    placeholder="Buscar por nombre, código..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-10"
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                />
                            </div>
                        </div>
                        
                        <div>
                            <Label htmlFor="status">Estado</Label>
                            <Select value={statusFilter} onValueChange={setStatusFilter}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todos</SelectItem>
                                    <SelectItem value="true">Activo</SelectItem>
                                    <SelectItem value="false">Inactivo</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <div className="flex items-end space-x-2">
                            <Button onClick={handleSearch} className="flex-1">
                                <Search className="h-4 w-4 mr-2" />
                                Buscar
                            </Button>
                            {hasActiveFilters && (
                                <Button variant="outline" onClick={clearFilters}>
                                    <X className="h-4 w-4" />
                                </Button>
                            )}
                        </div>
                    </div>
                </CardContent>
            )}
        </Card>
    );
}
