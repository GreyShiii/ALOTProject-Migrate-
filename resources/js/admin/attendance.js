const searchInput =
    document.getElementById("attendance-search")

const departmentFilter =
    document.getElementById("attendance-department")

const dateFilter =
    document.getElementById("attendance-date")

const statusFilter =
    document.getElementById("attendance-status")

const tableBody =
    document.getElementById("attendance-table-body")

const emptyMessage =
    document.getElementById("attendance-empty")

const paginationContainer =
    document.getElementById("attendance-pagination")

const ATTENDANCES_PER_PAGE =
    10

let attendances =
    []

let currentPage =
    1


async function loadAttendance() {

    try {

        const response =
            await fetch(
                "/admin/attendance/data",
                {
                    headers: {
                        Accept:
                            "application/json",
                    },
                }
            )


        if (
            !response.ok
        ) {

            console.error(
                "ADMIN ATTENDANCE API ERROR:",
                response.status
            )

            return
        }


        const data =
            await response.json()


        if (
            !data.success
        ) {

            console.error(
                "ADMIN ATTENDANCE API RETURNED AN ERROR:",
                data
            )

            return
        }


        attendances =
            data.attendances || []


        filterAttendance(
            1
        )

    } catch (
        error
    ) {

        console.error(
            "LOAD ADMIN ATTENDANCE ERROR:",
            error
        )
    }
}


function formatDate(
    date
) {

    if (
        !date
    ) {

        return "—"
    }


    return new Date(
        date
    ).toLocaleDateString(
        "en-US",
        {
            month: "short",
            day: "numeric",
            year: "numeric",
        }
    )
}


function formatTime(
    datetime
) {

    if (
        !datetime
    ) {

        return "—"
    }


    return new Date(
        datetime
    ).toLocaleTimeString(
        "en-US",
        {
            hour: "numeric",
            minute: "2-digit",
        }
    )
}


function calculateTotalHours(
    timeIn,
    timeOut
) {

    if (
        !timeIn ||
        !timeOut
    ) {

        return "—"
    }


    const start =
        new Date(
            timeIn
        )

    const end =
        new Date(
            timeOut
        )


    const totalMinutes =
        Math.floor(
            (
                end - start
            ) /
                (1000 * 60)
        )


    if (
        totalMinutes < 0
    ) {

        return "—"
    }


    const hours =
        Math.floor(
            totalMinutes / 60
        )


    const minutes =
        totalMinutes % 60


    return `${hours}h ${String(
        minutes
    ).padStart(
        2,
        "0"
    )}m`
}


function getAttendanceStatus(
    attendance
) {

    if (
        attendance.time_in &&
        attendance.time_out
    ) {

        return "completed"
    }


    if (
        attendance.time_in &&
        !attendance.time_out
    ) {

        return "working"
    }


    return "not_started"
}


function statusBadge(
    attendance
) {

    const status =
        getAttendanceStatus(
            attendance
        )


    if (
        status ===
        "completed"
    ) {

        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                Completed
            </span>
        `
    }


    if (
        status ===
        "working"
    ) {

        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">
                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                Working
            </span>
        `
    }

}


function createAttendanceRow(
    attendance
) {

    const employee =
        attendance.employee

    const user =
        employee?.user

    const department =
        employee?.department


    const employeeName =
        user
            ? `${user.first_name} ${user.last_name}`
            : "Unknown Employee"


    const row =
        document.createElement(
            "tr"
        )


    row.className =
        "transition hover:bg-gray-50"


    row.innerHTML = `
        <td class="px-5 py-3 text-sm text-gray-900 text-center">
            <p class="font-medium">
                ${employeeName}
            </p>

            <p class="mt-1 text-xs text-gray-500 text-center">
                ${user?.email || "—"}
            </p>
        </td>

        <td class="px-5 py-3 text-sm text-gray-700 text-center">
            ${department?.name || "—"}
        </td>

        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
            ${formatDate(attendance.date)}
        </td>

        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
            ${formatTime(attendance.time_in)}
        </td>

        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
            ${formatTime(attendance.time_out)}
        </td>

        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 text-center">
            ${calculateTotalHours(
                attendance.time_in,
                attendance.time_out
            )}
        </td>

        <td class="px-5 py-3 text-center">
            ${statusBadge(attendance)}
        </td>
    `


    return row
}


function renderAttendance(
    records
) {

    tableBody.innerHTML =
        ""


    if (
        records.length === 0
    ) {

        emptyMessage.classList.remove(
            "hidden"
        )

        return
    }


    emptyMessage.classList.add(
        "hidden"
    )


    records.forEach(
        (
            attendance
        ) => {

            tableBody.appendChild(
                createAttendanceRow(
                    attendance
                )
            )
        }
    )
}


function filterAttendance(
    page = 1
) {

    const searchValue =
        searchInput.value
            .toLowerCase()
            .trim()


    const departmentValue =
        departmentFilter.value
            .toLowerCase()
            .trim()


    const dateValue =
        dateFilter.value


    const statusValue =
        statusFilter.value
            .toLowerCase()
            .trim()


    const filteredRecords =
        attendances.filter(
            (
                attendance
            ) => {

                const user =
                    attendance.employee?.user

                const department =
                    attendance.employee?.department


                const employeeName =
                    user
                        ? `${user.first_name} ${user.last_name}`
                        : ""


                const employeeEmail =
                    user?.email || ""


                const departmentId =
                    String(
                        attendance.employee?.department_id ||
                        ""
                    )


                const attendanceDate =
                    attendance.date
                        ? String(
                            attendance.date
                        ).substring(
                            0,
                            10
                        )
                        : ""


                const attendanceStatus =
                    getAttendanceStatus(
                        attendance
                    )


                const searchText =
                    `${employeeName} ${employeeEmail}`
                        .toLowerCase()


                const matchesSearch =
                    searchText.includes(
                        searchValue
                    )


                const matchesDepartment =
                    departmentValue === "" ||
                    departmentId ===
                        departmentValue


                const matchesDate =
                    dateValue === "" ||
                    attendanceDate ===
                        dateValue


                const matchesStatus =
                    statusValue === "" ||
                    attendanceStatus ===
                        statusValue


                return (
                    matchesSearch &&
                    matchesDepartment &&
                    matchesDate &&
                    matchesStatus
                )
            }
        )


    const totalRecords =
        filteredRecords.length


    const totalPages =
        Math.ceil(
            totalRecords /
                ATTENDANCES_PER_PAGE
        )


    if (
        totalPages === 0
    ) {

        currentPage =
            1

    } else if (
        page > totalPages
    ) {

        currentPage =
            totalPages

    } else if (
        page < 1
    ) {

        currentPage =
            1

    } else {

        currentPage =
            page
    }


    const startIndex =
        (
            currentPage -
            1
        ) *
            ATTENDANCES_PER_PAGE


    const endIndex =
        startIndex +
        ATTENDANCES_PER_PAGE


    const pageRecords =
        filteredRecords.slice(
            startIndex,
            endIndex
        )


    renderAttendance(
        pageRecords
    )


    renderPagination(
        totalRecords,
        totalPages
    )
}


function renderPagination(
    totalRecords,
    totalPages
) {

    paginationContainer.innerHTML =
        ""


    if (
        totalRecords <=
        ATTENDANCES_PER_PAGE
    ) {

        paginationContainer.classList.add(
            "hidden"
        )

        return
    }


    paginationContainer.classList.remove(
        "hidden"
    )


    const wrapper =
        document.createElement(
            "div"
        )


    wrapper.className =
        "flex min-h-[72px] w-full items-center justify-between px-5 py-3"


    const endRecord =
        Math.min(
            currentPage *
                ATTENDANCES_PER_PAGE,
            totalRecords
        )


    const information =
        document.createElement(
            "p"
        )


    information.className =
        "text-xs text-gray-500"


    information.innerHTML =
        `
            Showing
            <span class="font-semibold text-gray-700">
                ${endRecord}
            </span>
            of
            <span class="font-semibold text-gray-700">
                ${totalRecords}
            </span>
            records
        `


    const controls =
        document.createElement(
            "div"
        )


    controls.className =
        "flex items-center gap-1"


    const previousButton =
        document.createElement(
            "button"
        )


    previousButton.type =
        "button"

    previousButton.disabled =
        currentPage <= 1


    previousButton.className =
        "inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-500 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"


    previousButton.innerHTML =
        `
            <span class="text-base leading-none">
                ‹
            </span>

            <span>
                Previous
            </span>
        `


    previousButton.addEventListener(
        "click",
        () => {

            filterAttendance(
                currentPage -
                    1
            )
        }
    )


    controls.appendChild(
        previousButton
    )


    for (
        let page = 1;
        page <= totalPages;
        page++
    ) {

        const pageButton =
            document.createElement(
                "button"
            )


        pageButton.type =
            "button"

        pageButton.textContent =
            page


        pageButton.className =
            "inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-xs font-medium transition"


        if (
            page ===
            currentPage
        ) {

            pageButton.classList.add(
                "border",
                "border-gray-200",
                "bg-gray-100",
                "text-gray-800",
                "shadow-sm"
            )

        } else {

            pageButton.classList.add(
                "text-gray-700",
                "hover:bg-gray-100"
            )
        }


        pageButton.addEventListener(
            "click",
            () => {

                filterAttendance(
                    page
                )
            }
        )


        controls.appendChild(
            pageButton
        )
    }


    const nextButton =
        document.createElement(
            "button"
        )


    nextButton.type =
        "button"

    nextButton.disabled =
        currentPage >=
        totalPages


    nextButton.className =
        "inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"


    nextButton.innerHTML =
        `
            <span>
                Next
            </span>

            <span class="text-base leading-none">
                ›
            </span>
        `


    nextButton.addEventListener(
        "click",
        () => {

            filterAttendance(
                currentPage +
                    1
            )
        }
    )


    controls.appendChild(
        nextButton
    )


    wrapper.appendChild(
        information
    )

    wrapper.appendChild(
        controls
    )


    paginationContainer.appendChild(
        wrapper
    )
}


searchInput.addEventListener(
    "input",
    () => {

        filterAttendance(
            1
        )
    }
)


departmentFilter.addEventListener(
    "change",
    () => {

        filterAttendance(
            1
        )
    }
)


dateFilter.addEventListener(
    "change",
    () => {

        filterAttendance(
            1
        )
    }
)


statusFilter.addEventListener(
    "change",
    () => {

        filterAttendance(
            1
        )
    }
)


loadAttendance()
