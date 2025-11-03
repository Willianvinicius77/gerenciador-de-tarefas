const API_URL = "http://localhost:3001/tarefas";
const listaTarefas = document.getElementById("listaTarefas");
const form = document.getElementById("taskForm");

async function carregarTarefas() {
  listaTarefas.innerHTML = "<li>Carregando tarefas...</li>";
  const res = await fetch(API_URL);
  const tarefas = await res.json();

  listaTarefas.innerHTML = "";

  tarefas.forEach(tarefa => {
    const li = document.createElement("li");
    li.className = tarefa.concluida ? "done" : "";

    li.innerHTML = `
      <div class="titulo">${tarefa.titulo}</div>
      <div class="desc">${tarefa.descricao || ""}</div>
      <small>Prioridade: ${tarefa.prioridade}</small>
      <div class="actions">
        <button onclick="toggleConcluida(${tarefa.id}, ${tarefa.concluida})">
          ${tarefa.concluida ? "Desmarcar" : "Concluir"}
        </button>
        <button onclick="deletarTarefa(${tarefa.id})" style="background:#ff6666">Excluir</button>
      </div>
    `;

    listaTarefas.appendChild(li);
  });
}

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const novaTarefa = {
    titulo: document.getElementById("titulo").value,
    descricao: document.getElementById("descricao").value,
    prioridade: parseInt(document.getElementById("prioridade").value),
  };

  const res = await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(novaTarefa),
  });

  if (res.ok) {
    form.reset();
    carregarTarefas();
  } else {
    alert("Erro ao criar tarefa!");
  }
});

async function toggleConcluida(id, concluida) {
  await fetch(`${API_URL}/${id}`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ concluida: !concluida }),
  });
  carregarTarefas();
}

async function deletarTarefa(id) {
  if (confirm("Tem certeza que deseja excluir esta tarefa?")) {
    await fetch(`${API_URL}/${id}`, { method: "DELETE" });
    carregarTarefas();
  }
}

// Carrega as tarefas na inicialização
carregarTarefas();
