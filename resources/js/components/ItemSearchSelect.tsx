import React, { useState, useRef, useEffect } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, ChevronDown, X } from 'lucide-react';

interface Item {
    id: number;
    code: string;
    name: string;
    price: number;
    unit?: string;
    display_name: string;
}

interface ItemSearchSelectProps {
    items: Item[];
    value: string;
    onValueChange: (value: string) => void;
    placeholder?: string;
    disabled?: boolean;
}

export default function ItemSearchSelect({ 
    items, 
    value, 
    onValueChange, 
    placeholder = "Buscar item...",
    disabled = false 
}: ItemSearchSelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [filteredItems, setFilteredItems] = useState<Item[]>(items);
    const [highlightedIndex, setHighlightedIndex] = useState(-1);
    const dropdownRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    // Filtrar items basado en el término de búsqueda
    useEffect(() => {
        if (!searchTerm.trim()) {
            setFilteredItems(items.slice(0, 50)); // Limitar a 50 items inicialmente
        } else {
            const searchLower = searchTerm.toLowerCase();
            const filtered = items.filter(item =>
                item.name.toLowerCase().includes(searchLower) ||
                item.code.toLowerCase().includes(searchLower) ||
                item.display_name.toLowerCase().includes(searchLower)
            ).slice(0, 50); // Limitar resultados para mejor rendimiento
            setFilteredItems(filtered);
        }
        setHighlightedIndex(-1);
    }, [searchTerm, items]);

    // Cerrar dropdown cuando se hace click fuera
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Obtener el item seleccionado
    const selectedItem = items.find(item => item.id.toString() === value);

    // Manejar selección de item
    const handleSelectItem = (item: Item) => {
        onValueChange(item.id.toString());
        setIsOpen(false);
        setSearchTerm('');
        setHighlightedIndex(-1);
        // Desenfocar el input para permitir que el siguiente campo reciba el focus
        inputRef.current?.blur();
    };

    // Limpiar selección
    const handleClear = () => {
        onValueChange('');
        setSearchTerm('');
        setIsOpen(false);
        setHighlightedIndex(-1);
        // Enfocar el input después de limpiar
        setTimeout(() => {
            inputRef.current?.focus();
        }, 50);
    };

    // Manejar teclas
    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (disabled) return;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (!isOpen) {
                    setIsOpen(true);
                } else {
                    setHighlightedIndex(prev => 
                        prev < filteredItems.length - 1 ? prev + 1 : 0
                    );
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (isOpen) {
                    setHighlightedIndex(prev => 
                        prev > 0 ? prev - 1 : filteredItems.length - 1
                    );
                }
                break;
            case 'Enter':
                e.preventDefault();
                if (isOpen && highlightedIndex >= 0 && filteredItems[highlightedIndex]) {
                    handleSelectItem(filteredItems[highlightedIndex]);
                } else if (!isOpen) {
                    setIsOpen(true);
                }
                break;
            case 'Escape':
                e.preventDefault();
                setIsOpen(false);
                setHighlightedIndex(-1);
                break;
        }
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <div className="relative">
                <Input
                    ref={inputRef}
                    type="text"
                    value={isOpen ? searchTerm : (selectedItem?.display_name || '')}
                    onChange={(e) => {
                        setSearchTerm(e.target.value);
                        if (!isOpen) setIsOpen(true);
                    }}
                    onFocus={() => {
                        if (!disabled) {
                            setIsOpen(true);
                            setSearchTerm('');
                        }
                    }}
                    onKeyDown={handleKeyDown}
                    placeholder={placeholder}
                    disabled={disabled}
                    className="pr-20"
                />
                <div className="absolute inset-y-0 right-0 flex items-center">
                    {selectedItem && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={handleClear}
                            className="h-8 w-8 p-0 hover:bg-gray-100"
                            disabled={disabled}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    )}
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => !disabled && setIsOpen(!isOpen)}
                        className="h-8 w-8 p-0 hover:bg-gray-100"
                        disabled={disabled}
                    >
                        {isOpen ? <ChevronDown className="h-4 w-4 rotate-180" /> : <ChevronDown className="h-4 w-4" />}
                    </Button>
                </div>
            </div>

            {isOpen && !disabled && (
                <div className="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto">
                    {filteredItems.length === 0 ? (
                        <div className="px-3 py-2 text-sm text-gray-500">
                            {searchTerm.trim() ? 'No se encontraron items' : 'Escribe para buscar items...'}
                        </div>
                    ) : (
                        <>
                            {filteredItems.map((item, index) => (
                                <div
                                    key={item.id}
                                    className={`px-3 py-2 cursor-pointer text-sm hover:bg-gray-100 ${
                                        index === highlightedIndex ? 'bg-blue-50' : ''
                                    } ${
                                        item.id.toString() === value ? 'bg-blue-100' : ''
                                    }`}
                                    onClick={() => handleSelectItem(item)}
                                    onMouseEnter={() => setHighlightedIndex(index)}
                                >
                                    <div className="font-medium">{item.name}</div>
                                    <div className="text-xs text-gray-500">
                                        {item.code} • ${item.price} • {item.unit}
                                    </div>
                                </div>
                            ))}
                            {/* Mostrar indicador si hay más resultados */}
                            {searchTerm.trim() && items.filter(item =>
                                item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                                item.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
                                item.display_name.toLowerCase().includes(searchTerm.toLowerCase())
                            ).length > 50 && (
                                <div className="px-3 py-2 text-xs text-gray-400 border-t">
                                    Mostrando primeros 50 resultados. Refina tu búsqueda para ver más.
                                </div>
                            )}
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
