// 🔹 Utility Functions

function loadFromLocalStorage(key) {
    return JSON.parse(localStorage.getItem(key)) || [];
}

function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validateFormData(data) {
    if (!data.name || !data.email || !data.id) {
        alert("يرجى ملء جميع الحقول المطلوبة.");
        return false;
    }
    if (!validateEmail(data.email)) {
        alert("يرجى إدخال بريد إلكتروني صالح.");
        return false;
    }
    return true;
}

function showAlert(message) {
    alert(message); // يمكنك استبدال هذا بكود مكتبة SweetAlert
}

function redirectTo(page) {
    window.history.pushState(null, "", page);
    window.location.reload();
}

function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// 🔹 Load Data

const volunteers = loadFromLocalStorage("volunteersList");
const opportunities = loadFromLocalStorage("opportunitiesList");

document.getElementById("totalVolunteers").textContent = volunteers.length;
document.getElementById("totalOpportunities").textContent = opportunities.length;

const activeCount = opportunities.filter(op => op.status === "active").length;
document.getElementById("activeOpportunities").textContent = activeCount;

// 🔹 Add / Edit Opportunity Page

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("opportunityForm");
    if (!form) return;

    const titleInput = document.getElementById("title");
    const dateInput = document.getElementById("date");
    const descInput = document.getElementById("description");
    const volInput = document.getElementById("volunteers");
    const hoursInput = document.getElementById("hours");
    const existingSelect = document.getElementById("existingOpportunity");

    let opportunities = loadFromLocalStorage("opportunitiesList");

    opportunities.forEach((op, index) => {
        const opt = document.createElement("option");
        opt.value = index;
        opt.textContent = op.title;
        existingSelect.appendChild(opt);
    });

    existingSelect.addEventListener("change", () => {
        const selected = existingSelect.value;
        if (selected === "") {
            form.reset();
        } else {
            const op = opportunities[selected];
            titleInput.value = op.title;
            dateInput.value = op.date;
            descInput.value = op.description;
            volInput.value = op.volunteers;
            hoursInput.value = op.hours;
        }
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const newData = {
            title: titleInput.value,
            date: dateInput.value,
            description: descInput.value,
            volunteers: volInput.value,
            hours: hoursInput.value,
            status: "active"
        };

        if (!validateFormData(newData)) return;

        const selected = existingSelect.value;
        if (selected === "") {
            opportunities.push(newData);
            showAlert("تم إضافة الفرصة بنجاح!");
        } else {
            opportunities[selected] = newData;
            showAlert("تم تحديث الفرصة بنجاح!");
        }

        localStorage.setItem("opportunitiesList", JSON.stringify(opportunities));
        form.reset();
        existingSelect.value = "";
    });
});

// 🔹 View Opportunities Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.querySelector("table");
    if (!table) return;

    const opportunities = loadFromLocalStorage("opportunitiesList");
    table.querySelectorAll("tr:not(:first-child)").forEach(tr => tr.remove());

    opportunities.forEach((op, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${op.title}</td>
            <td>${op.date}</td>
            <td>${op.description}</td>
            <td>${op.volunteers}</td>
            <td>${op.hours}</td>
            <td>
                <a href="Opportunity manage.html?edit=${index}" class="button">Edit</a>
                <a href="#" class="button delete-btn" data-index="${index}">Delete</a>
            </td>
        `;
        table.appendChild(row);
    });

    table.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-btn")) {
            const idx = e.target.getAttribute("data-index");
            if (confirm("هل أنت متأكد أنك تريد حذف هذه الفرصة؟")) {
                opportunities.splice(idx, 1);
                localStorage.setItem("opportunitiesList", JSON.stringify(opportunities));
                location.reload();
            }
        }
    });
});

// 🔹 View Volunteers Page
document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("volunteerTable");
    if (!tableBody) return;

    const volunteers = loadFromLocalStorage("volunteersList");
    tableBody.innerHTML = "";

    volunteers.forEach((vol, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${vol.name}</td>
            <td>${vol.id}</td>
            <td>${vol.email}</td>
            <td>${vol.college}</td>
            <td>${vol.phone}</td>
            <td>${vol.hours}</td>
            <td>
                <a href="volunteers manage.html?edit=${index}" class="button">Edit</a>
                <a href="#" class="button delete-btn" data-index="${index}">Delete</a>
            </td>
        `;
        tableBody.appendChild(row);
    });

    tableBody.addEventListener("click", function (e) {
        if (e.target.classList.contains("delete-btn")) {
            const idx = e.target.getAttribute("data-index");
            if (confirm("هل أنت متأكد أنك تريد حذف هذا المتطوع؟")) {
                volunteers.splice(idx, 1);
                localStorage.setItem("volunteersList", JSON.stringify(volunteers));
                location.reload();
            }
        }
    });
});

// 🔹 Add / Edit Volunteer Page
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    if (!form) return;

    const nameInput = document.getElementById("name");
    const idInput = document.getElementById("studentID");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const collegeInput = document.getElementById("college");
    const phoneInput = document.getElementById("phone");
    const selectVol = form.querySelector("select[name='volunteer']");

    let volunteers = loadFromLocalStorage("volunteersList");

    volunteers.forEach((vol, index) => {
        const opt = document.createElement("option");
        opt.value = index;
        opt.textContent = vol.name;
        selectVol.appendChild(opt);
    });

    selectVol.addEventListener("change", () => {
        const idx = selectVol.value;
        if (idx === "") {
            form.reset();
        } else {
            const vol = volunteers[idx];
            nameInput.value = vol.name;
            idInput.value = vol.id;
            emailInput.value = vol.email;
            passwordInput.value = vol.password;
            collegeInput.value = vol.college;
            phoneInput.value = vol.phone;
        }
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const newVol = {
            name: nameInput.value,
            id: idInput.value,
            email: emailInput.value,
            password: passwordInput.value,
            college: collegeInput.value,
            phone: phoneInput.value,
            hours: 0
        };

        if (!validateFormData(newVol)) return;

        const selected = selectVol.value;
        if (selected === "") {
            volunteers.push(newVol);
            showAlert("تم إضافة المتطوع بنجاح!");
        } else {
            volunteers[selected] = newVol;
            showAlert("تم تحديث المتطوع بنجاح!");
        }

        localStorage.setItem("volunteersList", JSON.stringify(volunteers));
        form.reset();
        selectVol.value = "";
    });
});

// 🔹 Student Login Page
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        if (email === "ad@ex.com" && password === "ad123") {
            redirectTo("Admin Dashboard.html");
            return;
        }

        const students = loadFromLocalStorage("studentsList");
        const student = students.find(st => st.email === email && st.password === password);

        if (student) {
            localStorage.setItem("studentData", JSON.stringify(student));
            redirectTo("Student Home.html");
        } else {
            showAlert("البريد الإلكتروني أو كلمة المرور غير صحيحة");
        }
    });
});

// 🔹 Student Opportunities Page
document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".container");
    if (!container) return;

    const student = JSON.parse(localStorage.getItem("studentData")) || {};
    let opportunities = loadFromLocalStorage("opportunitiesList");

    const cardGrids = container.querySelectorAll(".card-grid");
    cardGrids.forEach(grid => grid.innerHTML = "");

    const available = opportunities.filter(op => op.status === "active");
    const upcoming = opportunities.filter(op => op.status === "upcoming");

    function createOpportunityCard(op) {
        const card = document.createElement("div");
        card.className = "card";
        card.innerHTML = `
            <h3>${op.title}</h3>
            <p>${op.description}</p>
            <details>
                <summary>More Info</summary>
                <p><strong>Details:</strong> ${op.description}</p>
                <p><strong>Date:</strong> ${op.date}</p>
                <p><strong>Hours:</strong> ${op.hours}</p>
                <button class="join-btn">Join Now!</button>
            </details>
        `;
        const joinBtn = card.querySelector(".join-btn");
        joinBtn.addEventListener("click", () => {
            if (!student.participations) student.participations = [];
            if (!student.hours) student.hours = 0;

            if (!student.participations.includes(op.title)) {
                student.participations.push(op.title);
                student.hours += parseInt(op.hours) || 0;
                localStorage.setItem("studentData", JSON.stringify(student));
                showAlert(`لقد انضممت إلى "${op.title}"!`);
            } else {
                showAlert("أنت بالفعل مشترك في هذه الفرصة.");
            }
        });
        return card;
    }

    const availableGrid = container.querySelector(".card-grid:nth-of-type(1)");
    available.forEach(op => availableGrid.appendChild(createOpportunityCard(op)));

    const upcomingGrid = container.querySelector(".card-grid:nth-of-type(2)");
    upcoming.forEach(op => upcomingGrid.appendChild(createOpportunityCard(op)));
});

// 🔹 Student Profile Page
document.addEventListener("DOMContentLoaded", function () {
    const student = JSON.parse(localStorage.getItem("studentData")) || {};
    if (!student) return;

    const container = document.querySelector(".container");
    if (!container) return;

    const profileSection = container.querySelector("section");
    const form = profileSection.querySelector("form");

    const usernameP = profileSection.querySelector("p:nth-of-type(1)");
    const emailP = profileSection.querySelector("p:nth-of-type(2)");
    const phoneP = profileSection.querySelector("p:nth-of-type(3)");

    usernameP.textContent = `Username: ${student.name || ""}`;
    emailP.textContent = `Email: ${student.email || ""}`;
    phoneP ? phoneP.textContent = `Phone: ${student.phone || ""}` : null;

    form.username.value = student.name || "";
    form.email.value = student.email || "";
    form.phone.value = student.phone || "";

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        student.name = form.username.value || student.name;
        student.email = form.email.value || student.email;
        student.phone = form.phone.value || student.phone;

        localStorage.setItem("studentData", JSON.stringify(student));

        usernameP.textContent = `Username: ${student.name}`;
        emailP.textContent = `Email: ${student.email}`;
        if (phoneP) phoneP.textContent = `Phone: ${student.phone}`;

        showAlert("تم تحديث الملف الشخصي بنجاح!");
    });
});

// 🔹 Student Sign Up Page
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const name = form.name.value.trim();
        const studentID = form.studentID.value.trim();
        const email = form.email.value.trim();
        const password = form.password.value.trim();
        const phone = form.phone.value.trim();
        const college = form.college.value.trim();

        let students = loadFromLocalStorage("studentsList");

        if (students.some(st => st.email === email)) {
            showAlert("البريد الإلكتروني مسجل مسبقًا!");
            return;
        }

        const newStudent = {
            name,
            id: studentID,
            email,
            password,
            phone,
            college,
            hours: 0,
            participations: []
        };

        students.push(newStudent);
        localStorage.setItem("studentsList", JSON.stringify(students));
        localStorage.setItem("studentData", JSON.stringify(newStudent));

        showAlert("تم إنشاء الحساب بنجاح!");
        redirectTo("Student Home.html");
    });
});