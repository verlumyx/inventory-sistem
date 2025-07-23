import AppLogoIcon from './app-logo-icon';

interface AppLogoProps {
    variant?: 'default' | 'compact';
    showText?: boolean;
}

export default function AppLogo({ variant = 'default', showText = true }: AppLogoProps) {
    if (variant === 'compact') {
        return (
            <div className="flex items-center space-x-2">
                <div className="flex aspect-square size-10 items-center justify-center rounded-md bg-white dark:bg-gray-800 p-1.5 shadow-sm">
                    <AppLogoIcon className="size-8 object-contain" />
                </div>
            </div>
        );
    }

    return (
        <>
            <div className="flex aspect-square size-10 items-center justify-center rounded-md bg-white dark:bg-gray-800 p-1.5 shadow-sm">
                <AppLogoIcon className="size-8 object-contain" />
            </div>
        </>
    );
}
