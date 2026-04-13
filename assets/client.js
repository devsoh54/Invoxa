document.addEventListener("turbo:load", () => {
    const container = document.getElementById("clients-list");
    const overlay = document.getElementById("modal-overlay");
    const btnAdd = document.getElementById("btn-add-client");
    const btnEdit = document.getElementById("btn-add-edit");
    const btnClose = document.getElementById("modal-close");
    const form = document.getElementById("form-client");
    let currentClientId = null;

    if (!container) return;
    // ── Chargement de la liste ──
    async function loadClients() {
        const res = await fetch("/api/clients/list");
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
                        <th>Modifier</th>
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
                            <td><button class="btn-edit" data-id="${c.id}">Modifier</button></td>
                        </tr>
                    `,
                        )
                        .join("")}
                </tbody>
            </table>
        `;

        document.querySelectorAll(".btn-edit").forEach((btn) => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.dataset.id);
                const client = clients.find((c) => c.id === id);
                openEditModal(client);
            });
        });
    }

    loadClients();

    // ── Ouverture / fermeture modal ──
    btnAdd.addEventListener("click", () => {
        currentClientId = null;
        form.reset();
        overlay.classList.remove("hidden");
    });
    btnClose.addEventListener("click", () => overlay.classList.add("hidden"));
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) overlay.classList.add("hidden");
    });
    //btnEdit.addEventListener("click", () => {});

    // ── Soumission du formulaire ──
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const url = currentClientId
            ? `/api/clients/edit/${currentClientId}`
            : "/api/clients/create";
        await fetch(url, {
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

    function openEditModal(client) {
        document.getElementById("client-nom").value = client.name;
        document.getElementById("client-email").value = client.email;
        document.getElementById("client-tel").value = client.phone ?? "";
        document.getElementById("client-entreprise").value =
            client.company ?? "";
        currentClientId = client.id;
        overlay.classList.remove("hidden");
    }
});
