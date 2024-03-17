fetch("/connect.php")
  .then((response) => response.text())
  .then((data) => {
    console.log(data);

    const messageDiv = document.createElement("div");
    messageDiv.classList.add("hidden");

    messageDiv.textContent = data;

    messageDiv.classList.add("message");

    document.body.appendChild(messageDiv);
  });
