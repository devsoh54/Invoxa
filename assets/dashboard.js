import Chart from "chart.js/auto";

document.addEventListener("turbo:load", () => {
    const grid = document.getElementById("kpi-grid");
    if (!grid) return;

    async function loadKpi() {
        const res = await fetch("/api/dashboard/kpi");
        const kpi = await res.json();

        document.getElementById("kpi-ca-value").textContent = parseFloat(
            kpi.chiffreAffaires,
        ).toLocaleString("fr-FR", {
            style: "currency",
            currency: "EUR",
        });
        document.getElementById("kpi-envoyees-value").textContent =
            kpi.facturesEnvoyees;
        document.getElementById("kpi-attente-value").textContent =
            kpi.enAttente;
        document.getElementById("kpi-retard-value").textContent = kpi.enRetard;
    }

    async function loadGraphique() {
        const res = await fetch("/api/dashboard/ca-semaine");
        const data = await res.json();

        const labels = data.map((d) => `S${d.semaine}`);
        const valeurs = data.map((d) => parseFloat(d.total));

        const ctx = document.getElementById("chart-ca-semaine");
        if (!ctx) return;

        // Détruit le graphique précédent si turbo recharge la page
        if (ctx._chartInstance) {
            ctx._chartInstance.destroy();
        }

        ctx._chartInstance = new Chart(ctx, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        label: "CA (€)",
                        data: valeurs,
                        backgroundColor: "rgba(99, 102, 241, 0.15)",
                        borderColor: "#6366f1",
                        borderWidth: 2,
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                parseFloat(ctx.raw).toLocaleString("fr-FR", {
                                    style: "currency",
                                    currency: "EUR",
                                }),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) =>
                                val.toLocaleString("fr-FR", {
                                    style: "currency",
                                    currency: "EUR",
                                }),
                        },
                        grid: { color: "#f3f4f6" },
                    },
                    x: {
                        grid: { display: false },
                    },
                },
            },
        });
    }

    async function loadDernieresFactures() {
        const res = await fetch("/api/dashboard/dernieres-factures");
        const factures = await res.json();

        const statusLabels = {
            draft: "Brouillon",
            sent: "Envoyée",
            paid: "Payée",
            late: "En retard",
        };

        const container = document.getElementById("dernieres-factures");
        if (!container) return;

        container.innerHTML = factures
            .map(
                (f) => `
        <div class="derniere-facture-row">
            <div class="derniere-facture-client">${f.clientName}</div>
            <div class="derniere-facture-date">${f.date}</div>
            <div>
                <span class="badge badge-${f.status}">${statusLabels[f.status]}</span>
            </div>
            <div class="derniere-facture-montant">
                ${parseFloat(f.total).toLocaleString("fr-FR", { style: "currency", currency: "EUR" })}
            </div>
        </div>
    `,
            )
            .join("");
    }

    loadGraphique();
    loadKpi();
    loadDernieresFactures();
});
