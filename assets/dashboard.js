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

    loadKpi();
});
