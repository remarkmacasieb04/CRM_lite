const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

const dateTimeFormatter = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
});

const relativeFormatter = new Intl.RelativeTimeFormat('en', {
    numeric: 'auto',
});

export function formatCurrency(
    value: string | number | null | undefined,
    fallback = 'Not set',
): string {
    if (value === null || value === undefined || value === '') {
        return fallback;
    }

    const number = typeof value === 'number' ? value : Number(value);

    if (Number.isNaN(number)) {
        return fallback;
    }

    return currencyFormatter.format(number);
}

export function formatDate(
    value: string | null | undefined,
    fallback = 'Not set',
): string {
    if (!value) {
        return fallback;
    }

    return dateFormatter.format(new Date(value));
}

export function formatDateTime(
    value: string | null | undefined,
    fallback = 'Not set',
): string {
    if (!value) {
        return fallback;
    }

    return dateTimeFormatter.format(new Date(value));
}

export function formatRelativeTime(value: string | null | undefined): string {
    if (!value) {
        return 'just now';
    }

    const date = new Date(value);
    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const minutes = Math.round(seconds / 60);
    const hours = Math.round(minutes / 60);
    const days = Math.round(hours / 24);

    if (Math.abs(seconds) < 60) {
        return relativeFormatter.format(seconds, 'second');
    }

    if (Math.abs(minutes) < 60) {
        return relativeFormatter.format(minutes, 'minute');
    }

    if (Math.abs(hours) < 24) {
        return relativeFormatter.format(hours, 'hour');
    }

    return relativeFormatter.format(days, 'day');
}

export function formatFileSize(value: number | null | undefined): string {
    if (!value || value <= 0) {
        return '0 KB';
    }

    if (value < 1024) {
        return `${value} B`;
    }

    if (value < 1024 * 1024) {
        return `${(value / 1024).toFixed(1)} KB`;
    }

    return `${(value / (1024 * 1024)).toFixed(1)} MB`;
}
