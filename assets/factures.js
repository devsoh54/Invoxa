document.addEventListener("turbo:load", () => {
    const container = document.getElementById("factures-list");
    const overlay = document.getElementById("modal-overlay-facture");
    const btnAdd = document.getElementById("btn-add-facture");
    const btnClose = document.getElementById("modal-close-facture");
    const btnCancel = document.getElementById("btn-cancel-facture");
    const btnSubmit = document.getElementById("btn-submit-facture");
    const modalTitle = document.getElementById("modal-title-facture");
    const form = document.getElementById("form-facture");
    const overlayDelete = document.getElementById(
        "modal-overlay-facture-delete",
    );
    const btnCloseDelete = document.getElementById(
        "modal-close-delete-facture",
    );
    const btnConfirmeDelete = document.getElementById("confirmeDeleteFacture");
    if (!container) return;

    let currentFactureId = null;

    const statusLabels = {
        draft: "Brouillon",
        sent: "Envoyée",
        paid: "Payée",
        late: "En retard",
    };

    // ── Chargement des clients dans le select ──
    async function loadClients() {
        const res = await fetch("/api/clients/list");
        const clients = await res.json();
        const select = document.getElementById("facture-client");
        clients.forEach((c) => {
            const opt = document.createElement("option");
            opt.value = c.id;
            opt.textContent = c.name;
            select.appendChild(opt);
        });
    }
    loadClients();

    // ── Chargement de la liste des factures ──
    async function loadFactures() {
        const res = await fetch("/api/factures/list");
        const factures = await res.json();
        container.innerHTML = `
            <table class="factures-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    ${factures
                        .map(
                            (f) => `
                        <tr>
                            <td>#${String(f.id).padStart(4, "0")}</td>
                            <td>${f.clientName}</td>
                            <td>${f.date}</td>
                            <td>${f.dueDate}</td>
                            <td>${parseFloat(f.total).toLocaleString("fr-FR", { style: "currency", currency: "EUR" })}</td>
                            <td><span class="badge badge-${f.status}">${statusLabels[f.status]}</span></td>
                            <td><button class="btn-edit-facture" data-id="${f.id}">Modifier</button></td>
                            <td><button class="btn-delete-facture" data-id="${f.id}">Supprimer</button></td>
                        </tr>
                    `,
                        )
                        .join("")}
                </tbody>
            </table>
        `;
        document.querySelectorAll(".btn-edit-facture").forEach((btn) => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.dataset.id);
                const facture = factures.find((f) => f.id === id);
                openEditModal(facture);
            });
        });

        document.querySelectorAll(".btn-delete-facture").forEach((btn) => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.dataset.id);
                const facture = factures.find((c) => c.id === id);
                openDeleteModal(facture);
            });
        });
    }
    loadFactures();

    // ── Modal ──
    function openModal(edit = false) {
        modalTitle.textContent = edit
            ? "Modifier la facture"
            : "Nouvelle facture";
        btnSubmit.textContent = edit ? "Enregistrer" : "Créer";
        overlay.classList.remove("hidden");
    }
    function closeModal() {
        currentFactureId = null;
        form.reset();
        overlay.classList.add("hidden");
    }

    btnAdd.addEventListener("click", () => openModal(false));
    btnClose.addEventListener("click", closeModal);
    btnCancel.addEventListener("click", closeModal);
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) closeModal();
    });
    btnCloseDelete.addEventListener("click", () =>
        overlayDelete.classList.add("hidden"),
    );

    function openEditModal(facture) {
        currentFactureId = facture.id;
        document.getElementById("facture-client").value = facture.clientId;
        document.getElementById("facture-date").value = formatDateForInput(
            facture.date,
        );
        document.getElementById("facture-echeance").value = formatDateForInput(
            facture.dueDate,
        );
        document.getElementById("facture-total").value = facture.total;
        document.getElementById("facture-status").value = facture.status;
        openModal(true);
    }

    function openDeleteModal(facture) {
        document.getElementById("message-delete-facture").textContent =
            "Voulez-vous vraiment supprimer la facture de  " +
            facture.clientName +
            " ?";
        currentFactureId = facture.id;
        overlayDelete.classList.remove("hidden");
    }

    btnConfirmeDelete.addEventListener("click", async (e) => {
        e.preventDefault();

        const url = `/api/factures/delete/${currentFactureId}`;
        await fetch(url, {
            method: "GET",
            headers: { "Content-Type": "application/json" },
        });

        overlayDelete.classList.add("hidden");
        loadFactures();
    });

    // ── Soumission ──
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const url = currentFactureId
            ? `/api/factures/edit/${currentFactureId}`
            : "/api/factures/create";

        await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                client: document.getElementById("facture-client").value,
                date: document.getElementById("facture-date").value,
                echeance: document.getElementById("facture-echeance").value,
                total: document.getElementById("facture-total").value,
                status: document.getElementById("facture-status").value,
            }),
        });

        closeModal();
        loadFactures();
    });

    function formatDateForInput(date) {
        if (!date) return "";
        const [day, month, year] = date.split("/");
        return `${year}-${month}-${day}`;
    }
});
