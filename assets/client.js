document.addEventListener("turbo:load", () => {
    const container = document.getElementById("clients-list");
    const overlay = document.getElementById("modal-overlay");
    const btnAdd = document.getElementById("btn-add-client");
    const btnClose = document.getElementById("modal-close");
    const form = document.getElementById("form-client");

    if (!container) return;
    // ── Chargement de la liste ──
    async function loadClients() {
        const res = await fetch("/api/clients");
        const clients = await res.json();
        console.log(clients);
        container.innerHTML = `
            <table class="clients-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Entreprise</th>
                    </tr>
                </thead>
                <tbody>
                    ${clients
                        .map(
                            (c) => `
                        <tr>
                            <td>${c.name}</td>
                            <td>${c.email}</td>
                            <td>${c.phone ?? "—"}</td>
                            <td>${c.company ?? "—"}</td>
                        </tr>
                    `,
                        )
                        .join("")}
                </tbody>
            </table>
        `;
    }

    loadClients();

    // ── Ouverture / fermeture modal ──
    btnAdd.addEventListener("click", () => overlay.classList.remove("hidden"));
    btnClose.addEventListener("click", () => overlay.classList.add("hidden"));
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) overlay.classList.add("hidden");
    });

    // ── Soumission du formulaire ──
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        await fetch("/api/clients", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nom: document.getElementById("client-nom").value,
                email: document.getElementById("client-email").value,
                telephone: document.getElementById("client-tel").value || null,
                entreprise:
                    document.getElementById("client-entreprise").value || null,
            }),
        });

        form.reset();
        overlay.classList.add("hidden");
        loadClients(); // rafraîchit la liste
    });
});
