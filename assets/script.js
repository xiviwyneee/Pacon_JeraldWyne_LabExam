document.querySelectorAll("[data-password]").forEach((button) => {
    if (!button.querySelector(".eye-icon")) {
        button.innerHTML = `<svg class="eye-icon" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" focusable="false">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
            <path class="eye-slash" d="M3 3 21 21" />
        </svg>`;
    }
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.password);
        const show = input.type === "password";

        input.type = show ? "text" : "password";
        button.setAttribute("aria-pressed", String(show));
        button.setAttribute(
            "aria-label",
            `${show ? "Hide" : "Show"} ${input.id.replaceAll("_", " ")}`
        );
    });
});

const password = document.getElementById("password");
const confirmation = document.getElementById("confirm_password");

if (password && confirmation) {
    const validatePasswords = () => {
        const characterCount = [...password.value].length;
        const byteCount = new TextEncoder().encode(password.value).length;

        password.setCustomValidity(
            password.value && (characterCount < 8 || byteCount > 72)
                ? "Use at least 8 characters and no more than 72 bytes."
                : ""
        );

        confirmation.setCustomValidity(
            confirmation.value && confirmation.value !== password.value
                ? "Your passwords do not match."
                : ""
        );
    };

    password.addEventListener("input", validatePasswords);
    confirmation.addEventListener("input", validatePasswords);

    validatePasswords();
}

document.querySelectorAll("[data-dialog]").forEach((button) => {
    button.addEventListener("click", () => {
        document.getElementById(button.dataset.dialog).showModal();
    });
});

document.querySelectorAll("[data-close]").forEach((button) => {
    button.addEventListener("click", () => {
        button.closest("dialog").close();
    });
});

const loginForm = document.getElementById("login-form");
const rememberEmail = document.getElementById("remember-email");
const email = document.getElementById("email");

if (loginForm && rememberEmail && email) {
    try {
        const savedEmail = localStorage.getItem("pinescape-email");

        if (savedEmail) {
            if (!email.value) email.value = savedEmail;
            rememberEmail.checked = true;
        }

        rememberEmail.addEventListener("change", () => {
            if (!rememberEmail.checked) {
                localStorage.removeItem("pinescape-email");
            }
        });

        loginForm.addEventListener("submit", () => {
            try {
                if (rememberEmail.checked) {
                    localStorage.setItem("pinescape-email", email.value.trim());
                } else {
                    localStorage.removeItem("pinescape-email");
                }
            } catch {
            }
        });
    } catch {
    }
}

const authLayout = document.querySelector('.auth-layout');
if (authLayout) {
    const stage = document.createElement('div');
    stage.className = 'auth-stage';
    const fitLayout = () => {
        if (window.innerWidth <= 900) {
            document.body.classList.remove('auth-desktop');
            authLayout.style.transform = '';
            if (stage.parentNode) stage.replaceWith(authLayout);
            return;
        }
        if (!stage.parentNode) {
            authLayout.replaceWith(stage);
            stage.append(authLayout);
        }
        document.body.classList.add('auth-desktop');
        const naturalHeight = Math.max(1024, authLayout.scrollHeight);
        const scale = Math.min(window.innerWidth / 1440,
            window.innerHeight / naturalHeight, 1.35);
        stage.style.width = `${1440 * scale}px`;
        stage.style.height = `${naturalHeight * scale}px`;
        authLayout.style.transform = `scale(${scale})`;
    };
    window.addEventListener('resize', fitLayout);
    new ResizeObserver(fitLayout).observe(authLayout);
    document.fonts.ready.then(fitLayout);
    fitLayout();
}
