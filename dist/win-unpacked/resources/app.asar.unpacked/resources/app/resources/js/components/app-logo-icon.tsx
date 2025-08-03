import { ImgHTMLAttributes } from 'react';

interface AppLogoIconProps extends Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'> {
    className?: string;
}

export default function AppLogoIcon({ className, ...props }: AppLogoIconProps) {
    return (
        <img
            src="/logo.png"
            alt="Logo Sistema de Inventario"
            className={className}
            {...props}
        />
    );
}
