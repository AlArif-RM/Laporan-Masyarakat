const toneClasses = {
    blue: 'bg-blue-100 text-blue-700 ring-blue-200',
    amber: 'bg-amber-100 text-amber-700 ring-amber-200',
    emerald: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    red: 'bg-rose-100 text-rose-700 ring-rose-200',
    slate: 'bg-slate-100 text-slate-700 ring-slate-200',
};

export default function StatusBadge({ meta, className = '' }) {
    const tone = toneClasses[meta?.tone] || toneClasses.slate;

    return (
        <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${tone} ${className}`}>
            {meta?.label || '-'}
        </span>
    );
}
