import { showToast } from "../employee/employees";

const actionArea = document.getElementById("attendance-action-area");

const statusText = document.getElementById("attendance-status-text");

const timeInText = document.getElementById("attendance-time-in");

const timeOutText = document.getElementById("attendance-time-out");

const totalHoursText = document.getElementById("attendance-total-hours");

const mySearch = document.getElementById("my-attendance-search");

const myDate = document.getElementById("my-attendance-date");

const myStatus = document.getElementById("my-attendance-status");

const myTableBody = document.getElementById("my-attendance-table-body");

const myEmpty = document.getElementById("my-attendance-empty");

const myPagination = document.getElementById("my-attendance-pagination");

const teamSearch = document.getElementById("team-attendance-search");

const teamDate = document.getElementById("team-attendance-date");

const teamStatus = document.getElementById("team-attendance-status");

const teamTableBody = document.getElementById("team-attendance-table-body");

const teamEmpty = document.getElementById("team-attendance-empty");

const teamPagination = document.getElementById("team-attendance-pagination");

let mySearchTimeout;

let teamSearchTimeout;

function formatDate(date) {
    if (!date) {
        return "—";
    }

    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

function formatTime(datetime) {
    if (!datetime) {
        return "—";
    }

    return new Date(datetime).toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
    });
}

function calculateHours(timeIn, timeOut) {
    if (!timeIn || !timeOut) {
        return "—";
    }

    const start = new Date(timeIn);

    const end = new Date(timeOut);

    const minutes = Math.floor((end - start) / 60000);

    if (minutes < 0) {
        return "—";
    }

    const hours = Math.floor(minutes / 60);

    const remaining = minutes % 60;

    return `${hours}h ${String(remaining).padStart(2, "0")}m`;
}

function getStatus(attendance) {
    if (attendance.time_in && attendance.time_out) {
        return "completed";
    }

    if (attendance.time_in && !attendance.time_out) {
        return "working";
    }

    return "not_started";
}

function statusBadge(status) {
    if (status === "completed") {
        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                Completed
            </span>
        `;
    }

    if (status === "working") {
        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">
                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                Working
            </span>
        `;
    }

}

function createMyRow(attendance) {
    const status = getStatus(attendance);

    const row = document.createElement("tr");

    row.className = "transition hover:bg-gray-50";

    row.innerHTML = `
        <td class="px-4 py-4 text-center text-sm font-medium text-gray-900">
            ${formatDate(attendance.date)}
        </td>

        <td class="px-4 py-4 text-center text-sm text-gray-700">
            ${formatTime(attendance.time_in)}
        </td>

        <td class="px-4 py-4 text-center text-sm text-gray-700">
            ${formatTime(attendance.time_out)}
        </td>

        <td class="px-4 py-4 text-center text-sm text-gray-700">
            ${calculateHours(attendance.time_in, attendance.time_out)}
        </td>

        <td class="px-4 py-4 text-center">
            <div class="flex justify-center">
                ${statusBadge(status)}
            </div>
        </td>
    `;

    return row;
}

function createTeamRow(attendance) {
    const employee = attendance.employee;

    const user = employee?.user;

    const department = employee?.department;

    const employeeName = user
        ? `${user.first_name} ${user.last_name}`
        : "Unknown Employee";

    const status = getStatus(attendance);

    const row = document.createElement("tr");

    row.className = "transition hover:bg-gray-50";

    row.innerHTML = `
        <td class="px-4 py-4 text-center">
            <p class="text-sm font-semibold text-gray-900">
                ${employeeName}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                ${user?.email || "—"}
            </p>
        </td>

        <td class="px-4 py-4 text-center text-sm text-gray-700">
            ${department?.name || "—"}
        </td>

        <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-700">
            ${formatDate(attendance.date)}
        </td>

        <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-700">
            ${formatTime(attendance.time_in)}
        </td>

        <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-700">
            ${formatTime(attendance.time_out)}
        </td>

        <td class="px-4 py-4 text-center">
            <div class="flex justify-center">
                ${statusBadge(status)}
            </div>
        </td>
    `;

    return row;
}

function renderPagination(container, pagination, loadPage) {
    container.innerHTML = "";

    if (!pagination || pagination.last_page <= 1) {
        container.classList.add("hidden");

        return;
    }

    container.classList.remove("hidden");

    const wrapper = document.createElement("div");

    wrapper.className =
        "flex min-h-[72px] w-full items-center justify-between px-5 py-3";

    const endRecord = Math.min(
        pagination.current_page * pagination.per_page,
        pagination.total,
    );

    const information = document.createElement("p");

    information.className = "text-xs text-gray-500";

    information.innerHTML = `
        Showing
        <span class="font-semibold text-gray-700">
            ${endRecord}
        </span>
        of
        <span class="font-semibold text-gray-700">
            ${pagination.total}
        </span>
        records
    `;

    const controls = document.createElement("div");

    controls.className = "flex items-center gap-1";

    const previousButton = document.createElement("button");

    previousButton.type = "button";

    previousButton.disabled = pagination.current_page <= 1;

    previousButton.className =
        "inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-500 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50";

    previousButton.innerHTML = `
        <span class="text-base leading-none">
            ‹
        </span>

        <span>
            Previous
        </span>
    `;

    previousButton.addEventListener("click", () => {
        loadPage(pagination.current_page - 1);
    });

    controls.appendChild(previousButton);

    for (let page = 1; page <= pagination.last_page; page++) {
        const pageButton = document.createElement("button");

        pageButton.type = "button";

        pageButton.textContent = page;

        pageButton.className =
            "inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-xs font-medium transition";

        if (page === pagination.current_page) {
            pageButton.classList.add(
                "border",
                "border-gray-200",
                "bg-gray-100",
                "text-gray-800",
                "shadow-sm",
            );
        } else {
            pageButton.classList.add("text-gray-700", "hover:bg-gray-100");
        }

        pageButton.addEventListener("click", () => {
            loadPage(page);
        });

        controls.appendChild(pageButton);
    }

    const nextButton = document.createElement("button");

    nextButton.type = "button";

    nextButton.disabled = pagination.current_page >= pagination.last_page;

    nextButton.className =
        "inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50";

    nextButton.innerHTML = `
        <span>
            Next
        </span>

        <span class="text-base leading-none">
            ›
        </span>
    `;

    nextButton.addEventListener("click", () => {
        loadPage(pagination.current_page + 1);
    });

    controls.appendChild(nextButton);

    wrapper.appendChild(information);

    wrapper.appendChild(controls);

    paginationContainer.appendChild(wrapper);
}

async function loadMyAttendance(page = 1) {
    const params = new URLSearchParams();

    const search = mySearch.value.trim();

    const date = myDate.value;

    const status = myStatus.value;

    if (search !== "") {
        params.set("search", search);
    }

    if (date !== "") {
        params.set("date", date);
    }

    if (status !== "") {
        params.set("status", status);
    }

    params.set("scope", "mine");

    params.set("page", page);

    try {
        const response = await fetch(
            `/manager/attendance/data?${params.toString()}`,
            {
                headers: {
                    Accept: "application/json",
                },
            },
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            console.error(data);

            return;
        }

        myTableBody.innerHTML = "";

        if (!data.attendances || data.attendances.length === 0) {
            myEmpty.classList.remove("hidden");
        } else {
            myEmpty.classList.add("hidden");

            data.attendances.forEach((attendance) => {
                myTableBody.appendChild(createMyRow(attendance));
            });
        }

        renderPagination(myPagination, data.pagination, loadMyAttendance);
    } catch (error) {
        console.error("LOAD MY ATTENDANCE ERROR:", error);
    }
}

async function loadTeamAttendance(page = 1) {
    const params = new URLSearchParams();

    const search = teamSearch.value.trim();

    const date = teamDate.value;

    const status = teamStatus.value;

    if (search !== "") {
        params.set("search", search);
    }

    if (date !== "") {
        params.set("date", date);
    }

    if (status !== "") {
        params.set("status", status);
    }

    params.set("scope", "team");

    params.set("page", page);

    try {
        const response = await fetch(
            `/manager/attendance/data?${params.toString()}`,
            {
                headers: {
                    Accept: "application/json",
                },
            },
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            console.error(data);

            return;
        }

        teamTableBody.innerHTML = "";

        if (!data.attendances || data.attendances.length === 0) {
            teamEmpty.classList.remove("hidden");
        } else {
            teamEmpty.classList.add("hidden");

            data.attendances.forEach((attendance) => {
                teamTableBody.appendChild(createTeamRow(attendance));
            });
        }

        renderPagination(teamPagination, data.pagination, loadTeamAttendance);
    } catch (error) {
        console.error("LOAD TEAM ATTENDANCE ERROR:", error);
    }
}

function updateAttendanceUI(attendance) {
    timeInText.textContent = formatTime(attendance.time_in);

    timeOutText.textContent = formatTime(attendance.time_out);

    totalHoursText.textContent = calculateHours(
        attendance.time_in,
        attendance.time_out,
    );

    if (attendance.time_in && attendance.time_out) {
        statusText.textContent = "Completed";

        actionArea.innerHTML = `
            <p class="text-sm text-gray-500">
                Your attendance for today is complete.
            </p>
        `;

        return;
    }

    if (attendance.time_in) {
        statusText.textContent = "Working";

        actionArea.innerHTML = `
            <form
                id="manager-time-out-form"
                method="POST"
                action="/manager/attendance/time-out"
            >

                <input
                    type="hidden"
                    name="_token"
                    value="${
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || ""
                    }"
                >

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    Time Out
                </button>

            </form>
        `;
    }
}

document.addEventListener("submit", async (event) => {
    const form = event.target;

    if (
        form.id !== "manager-time-in-form" &&
        form.id !== "manager-time-out-form"
    ) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector("button");

    if (!button) {
        return;
    }

    const isTimeIn = form.id === "manager-time-in-form";

    const originalText = isTimeIn ? "Time In" : "Time Out";

    button.disabled = true;

    button.textContent = "Recording...";

    try {
        const response = await fetch(form.action, {
            method: "POST",

            body: new FormData(form),

            headers: {
                Accept: "application/json",

                "X-Requested-With": "XMLHttpRequest",
            },
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            showToast(data.message || "Unable to update attendance.", "error");

            button.disabled = false;

            button.textContent = originalText;

            return;
        }

        updateAttendanceUI(data.attendance);

        showToast(data.message);

        await loadMyAttendance(1);

        await loadTeamAttendance(1);
    } catch (error) {
        console.error("ATTENDANCE ACTION ERROR:", error);

        showToast("Something went wrong while updating attendance.", "error");

        button.disabled = false;

        button.textContent = originalText;
    }
});

mySearch.addEventListener("input", () => {
    clearTimeout(mySearchTimeout);

    mySearchTimeout = setTimeout(() => {
        loadMyAttendance(1);
    }, 300);
});

myDate.addEventListener("change", () => {
    loadMyAttendance(1);
});

myStatus.addEventListener("change", () => {
    loadMyAttendance(1);
});

teamSearch.addEventListener("input", () => {
    clearTimeout(teamSearchTimeout);

    teamSearchTimeout = setTimeout(() => {
        loadTeamAttendance(1);
    }, 300);
});

teamDate.addEventListener("change", () => {
    loadTeamAttendance(1);
});

teamStatus.addEventListener("change", () => {
    loadTeamAttendance(1);
});

loadMyAttendance(1);

loadTeamAttendance(1);
