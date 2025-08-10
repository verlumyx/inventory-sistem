import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Formats a number as currency with just the $ symbol
 */
export function formatCurrency(amount: number): string {
    return `$${amount.toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}

/**
 * Formats a number as Venezuelan currency (Bs.)
 */
export function formatCurrencyVES(amount: number): string {
    return `Bs. ${amount.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}
