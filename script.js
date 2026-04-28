const filterstock = document.getElementById("filter-stock");
const productCards = document.querySelectorAll(".product");
const hamburger = document.querySelector(".hamburger");
const nav = document.querySelector(".nav");

if (hamburger && nav) {
    hamburger.addEventListener("click", () => {
        if (nav.classList.toggle("active")) {
            hamburger.innerHTML = "&#10006;";
        } else {
            hamburger.innerHTML = "&#9776;";
        }
    });
}

productCards.forEach(product => {
    product.addEventListener("click", (e) => {
        // Ignore clicks inside the form so the card does not open
        if (
            e.target.classList.contains("add") ||
            e.target.tagName === "INPUT" ||
            e.target.tagName === "LABEL"
        ) {
            return;
        }

        const id = product.dataset.id;
        window.location.href = "Item.php?id=" + id;
    });
});

if (filterstock) {
    filterstock.addEventListener("change", () => {
        const value = filterstock.value;

        productCards.forEach(card => {
            // Match against the simple stock value from the page
            const stock = card.dataset.stock;

            if (value === "all" || stock === value) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    });
}
