document.addEventListener("turbo:load", async () => {
    const container = document.getElementById("clients-list");
    if (!container) return;

    const response = await fetch("/api/clients");
    const clients = await response.json();
    console.log(clients);
    if (clients.length === 0) {
        container.innerHTML = "<p>Pas de clients enregistrés...</p>";
    } else {
        container.innerHTML = clients
            .map(
                (client) => `
        <div class="client-card">
            <p>${client.name}</p>
            <p>${client.email}</p>
        </div>
    `,
            )
            .join("");
    }
});
