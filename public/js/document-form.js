document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#document-form');
    if (!form) return;

    const rows = document.querySelector('#document-lines');
    const template = document.querySelector('#line-template');
    const addButton = document.querySelector('#add-line');
    const currency = form.dataset.currency || 'EUR';
    const decimals = currency === 'XOF' ? 0 : 2;
    const symbol = currency === 'XOF' ? 'FCFA' : currency === 'EUR' ? '€' : currency;
    const factor = decimals === 0 ? 1 : 100;
    const money = value => `${value.toLocaleString('fr-FR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })} ${symbol}`;

    const updateRow = row => {
        const option = row.querySelector('.line-article').selectedOptions[0];
        const quantity = Number(row.querySelector('.line-quantity').value || 0);
        const unit = option?.dataset.unit;
        const price = Number(option?.dataset.price || 0);
        const tax = Number(option?.dataset.tax || 0);
        const subtotal = Math.round(price * quantity * factor) / factor;
        const total = Math.round(subtotal * (1 + tax / 100) * factor) / factor;

        row.querySelector('.line-unit').textContent = unit || '—';
        row.querySelector('.line-price').textContent = option?.value ? money(price) : '—';
        row.querySelector('.line-tax').textContent = option?.value ? `${tax.toLocaleString('fr-FR')} %` : '—';
        row.querySelector('.line-total').textContent = option?.value ? money(total) : '—';
        return { subtotal, tax: Math.round((total - subtotal) * factor) / factor, total };
    };

    const updateTotals = () => {
        const totals = [...rows.querySelectorAll('.line-row')].map(updateRow);
        const sum = key => totals.reduce((value, line) => value + line[key], 0);
        document.querySelector('#subtotal-preview').textContent = money(sum('subtotal'));
        document.querySelector('#tax-preview').textContent = money(sum('tax'));
        document.querySelector('#total-preview').textContent = money(sum('total'));
    };

    rows.addEventListener('change', updateTotals);
    rows.addEventListener('input', updateTotals);
    rows.addEventListener('click', event => {
        if (!event.target.closest('.line-remove')) return;
        if (rows.querySelectorAll('.line-row').length === 1) {
            const row = event.target.closest('.line-row');
            row.querySelector('.line-article').value = '';
            row.querySelector('.line-quantity').value = '1';
        } else {
            event.target.closest('.line-row').remove();
        }
        updateTotals();
    });

    addButton.addEventListener('click', () => {
        const index = Math.max(...[...rows.querySelectorAll('[name^="lines["]')].map(input => {
            const match = input.name.match(/^lines\[(\d+)]/);
            return match ? Number(match[1]) : -1;
        }), -1) + 1;
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
        updateTotals();
    });

    updateTotals();
});
