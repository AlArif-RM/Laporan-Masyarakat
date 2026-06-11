import { usePage } from '@inertiajs/react';

const tones = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
    warning: 'border-amber-200 bg-amber-50 text-amber-700',
    error: 'border-rose-200 bg-rose-50 text-rose-700',
};

export default function FlashBanner() {
    const { flash } = usePage().props;

    const messages = [
        ['success', flash?.success],
        ['warning', flash?.warning],
        ['error', flash?.error],
    ].filter(([, message]) => Boolean(message));

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="mb-5 space-y-3">
            {messages.map(([type, message]) => (
                <div key={`${type}-${message}`} className={`rounded-2xl border px-4 py-3 text-sm ${tones[type]}`}>
                    {message}
                </div>
            ))}
        </div>
    );
}
