window.filamentChartJsPlugins ??= [];

window.filamentChartJsPlugins.push({
    id: 'maestroCenterText',
    afterDraw(chart, _args, options) {
        if (!options?.display || chart.config.type !== 'doughnut') {
            return;
        }

        const values = chart.data.datasets[0]?.data ?? [];
        const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
        const { ctx, chartArea } = chart;
        const centerX = (chartArea.left + chartArea.right) / 2;
        const centerY = (chartArea.top + chartArea.bottom) / 2;
        const canvasStyles = getComputedStyle(chart.canvas);
        const textColor = canvasStyles.color || '#172033';
        const secondaryColor = canvasStyles.getPropertyValue('--maestro-chart-secondary').trim() || '#64748B';

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = textColor;
        ctx.font = '700 28px Inter, sans-serif';
        ctx.fillText(total.toLocaleString('pt-PT'), centerX, centerY - 10);
        ctx.fillStyle = secondaryColor;
        ctx.font = '500 12px Inter, sans-serif';
        ctx.fillText(options.label, centerX, centerY + 17);
        ctx.restore();
    },
});
