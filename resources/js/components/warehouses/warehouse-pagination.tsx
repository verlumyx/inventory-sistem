import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    has_more_pages: boolean;
}

interface WarehousePaginationProps {
    pagination: Pagination;
    filters?: Record<string, any>;
    onPageChange?: (page: number) => void;
}

export default function WarehousePagination({ 
    pagination, 
    filters = {},
    onPageChange 
}: WarehousePaginationProps) {
    const handlePageChange = (page: number) => {
        if (onPageChange) {
            onPageChange(page);
        } else {
            const params = { ...filters, page };
            router.get('/warehouses', params, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const generatePageNumbers = () => {
        const pages = [];
        const { current_page, last_page } = pagination;
        
        // Mostrar hasta 5 páginas alrededor de la página actual
        let start = Math.max(1, current_page - 2);
        let end = Math.min(last_page, current_page + 2);
        
        // Ajustar si estamos cerca del inicio o final
        if (current_page <= 3) {
            end = Math.min(5, last_page);
        }
        if (current_page >= last_page - 2) {
            start = Math.max(1, last_page - 4);
        }
        
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        
        return pages;
    };

    if (pagination.last_page <= 1) {
        return null;
    }

    const pageNumbers = generatePageNumbers();

    return (
        <div className="flex items-center justify-between">
            <div className="text-sm text-muted-foreground">
                {pagination.total > 0 ? (
                    <>
                        Mostrando {pagination.from} - {pagination.to} de {pagination.total} resultados
                    </>
                ) : (
                    'No hay resultados'
                )}
            </div>
            
            <div className="flex items-center space-x-2">
                {/* Primera página */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(1)}
                    disabled={pagination.current_page === 1}
                    className="hidden sm:flex"
                >
                    <ChevronsLeft className="h-4 w-4" />
                </Button>
                
                {/* Página anterior */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(pagination.current_page - 1)}
                    disabled={pagination.current_page === 1}
                >
                    <ChevronLeft className="h-4 w-4" />
                    <span className="hidden sm:inline ml-1">Anterior</span>
                </Button>

                {/* Números de página */}
                <div className="hidden md:flex items-center space-x-1">
                    {pageNumbers[0] > 1 && (
                        <>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(1)}
                            >
                                1
                            </Button>
                            {pageNumbers[0] > 2 && (
                                <span className="px-2 text-muted-foreground">...</span>
                            )}
                        </>
                    )}
                    
                    {pageNumbers.map((page) => (
                        <Button
                            key={page}
                            variant={page === pagination.current_page ? "default" : "outline"}
                            size="sm"
                            onClick={() => handlePageChange(page)}
                        >
                            {page}
                        </Button>
                    ))}
                    
                    {pageNumbers[pageNumbers.length - 1] < pagination.last_page && (
                        <>
                            {pageNumbers[pageNumbers.length - 1] < pagination.last_page - 1 && (
                                <span className="px-2 text-muted-foreground">...</span>
                            )}
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(pagination.last_page)}
                            >
                                {pagination.last_page}
                            </Button>
                        </>
                    )}
                </div>

                {/* Información de página en móvil */}
                <div className="md:hidden text-sm text-muted-foreground">
                    {pagination.current_page} / {pagination.last_page}
                </div>

                {/* Página siguiente */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(pagination.current_page + 1)}
                    disabled={!pagination.has_more_pages}
                >
                    <span className="hidden sm:inline mr-1">Siguiente</span>
                    <ChevronRight className="h-4 w-4" />
                </Button>
                
                {/* Última página */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(pagination.last_page)}
                    disabled={pagination.current_page === pagination.last_page}
                    className="hidden sm:flex"
                >
                    <ChevronsRight className="h-4 w-4" />
                </Button>
            </div>
        </div>
    );
}
