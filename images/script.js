document.addEventListener("DOMContentLoaded", () => {
  const statuses = [
    { code: "", label: "—", color: "#e5e7eb" },
    { code: "H", label: "Home Office", color: "#99f6e4" },
    { code: "A", label: "Büro / Arbeit", color: "#a7f3d0" },
    { code: "U", label: "Urlaub", color: "#f9a8d4" },
    { code: "HU", label: "1/2 Tag Urlaub", color: "#d8b4fe" },
    { code: "GU", label: "Geplanter Urlaub", color: "#c084fc" },
    { code: "K", label: "Krank", color: "#fde047" },
    { code: "KK", label: "Kind Krank", color: "#facc15" },
    { code: "F", label: "FZA", color: "#bae6fd" }
  ];

  const getStatusLabel = (code) =>
    statuses.find((s) => s.code === code)?.label || "—";

  // Berechne das Datum eines bestimmten Wochentags (1 = Mo) in einer Kalenderwoche und Jahr
  const getDateOfISOWeek = (week, year, weekday) => {
    const jan4 = new Date(year, 0, 4);
    const jan4Day = jan4.getDay() || 7;
    const mondayOfWeek1 = new Date(jan4);
    mondayOfWeek1.setDate(jan4.getDate() - jan4Day + 1);
    const resultDate = new Date(mondayOfWeek1);
    resultDate.setDate(mondayOfWeek1.getDate() + (week - 1) * 7 + (weekday - 1));
    return resultDate;
  };

  // ---------- Benachrichtigungssystem ----------
  const notificationsContainer = document.createElement("div");
  notificationsContainer.id = "notifications";
  notificationsContainer.style.position = "fixed";
  notificationsContainer.style.top = "20px";
  notificationsContainer.style.left = "20px";
  notificationsContainer.style.zIndex = "9999";
  notificationsContainer.style.maxWidth = "300px";
  notificationsContainer.style.display = "flex";
  notificationsContainer.style.flexDirection = "column";
  notificationsContainer.style.gap = "10px";

  const clearBtn = document.createElement("button");
  clearBtn.textContent = "Alle als gelesen";
  clearBtn.style.padding = "6px 10px";
  clearBtn.style.borderRadius = "6px";
  clearBtn.style.border = "none";
  clearBtn.style.background = "#4b5563";
  clearBtn.style.color = "white";
  clearBtn.style.cursor = "pointer";
  clearBtn.addEventListener("click", () => {
    notificationsContainer.querySelectorAll(".notification").forEach((n) => n.remove());
  });

  notificationsContainer.appendChild(clearBtn);
  document.body.appendChild(notificationsContainer);

  const showNotification = ({ user_name, date, old_status, new_status }) => {
    const div = document.createElement("div");
    div.className = "notification";
    const formatted = new Date(date).toLocaleDateString("de-DE");
    div.textContent = `${user_name}s Status am ${formatted} wurde von ${getStatusLabel(old_status)} zu ${getStatusLabel(new_status)} geändert`;
    div.style.background = "#facc15";
    div.style.color = "#1f2937";
    div.style.padding = "10px 14px";
    div.style.borderRadius = "8px";
    div.style.boxShadow = "0 4px 8px rgba(0,0,0,0.1)";
    div.style.fontSize = "14px";
    div.style.fontWeight = "bold";
    div.style.animation = "fadeIn 0.3s ease-in-out";
    notificationsContainer.insertBefore(div, clearBtn.nextSibling);
  };

  const pollNotifications = () => {
    fetch("get_notifications.php")
      .then((res) => res.json())
      .then((data) => {
        data.forEach((n) => showNotification(n));
      });
  };

  setInterval(pollNotifications, 5000);

  // ---------- Bestehende Anwesenheitslogik ----------
  fetch("load_users.php")
    .then((res) => res.json())
    .then((users) => {
      document.querySelectorAll(".month-body").forEach((body) => {
        const weeks = body.dataset.weeks.split(",");
        const year = new Date().getFullYear(); // für Datumsermittlung

        Promise.all(
          weeks.map((w) =>
            fetch(`get_attendance.php?week=${w}`).then((r) => r.json())
          )
        ).then((attendancePerWeek) => {
          users.forEach((user) => {
            const row = document.createElement("div");
            row.className = "user-row";

            const userAvatarContainer = document.createElement("div");
            userAvatarContainer.className = "user-avatar-container";

            const img = document.createElement("img");
            img.src = `images/${user.image}`;
            img.alt = user.name;
            img.title = user.name;
            img.onerror = () => (img.src = "images/default.jpg");
            img.className = "user-avatar";

            const tooltip = document.createElement("span");
            tooltip.className = "user-avatar-tooltip";
            tooltip.textContent = user.name;

            userAvatarContainer.appendChild(img);
            userAvatarContainer.appendChild(tooltip);
            row.appendChild(userAvatarContainer);

            const weekContainer = document.createElement("div");
            weekContainer.className = "user-weeks";

            weeks.forEach((week, weekIndex) => {
              const weekCell = document.createElement("div");
              weekCell.className = "week-cell";

              const isHoliday = attendancePerWeek[weekIndex].some(
                (entry) => entry.isHoliday
              );

              for (let d = 1; d <= 5; d++) {
                const entryList = attendancePerWeek[weekIndex];
                const entry = entryList.find(
                  (a) => a.user_id == user.id && a.day == d
                );
                const current = entry ? entry.status : "";
                const status =
                  statuses.find((s) => s.code === current) || statuses[0];

                const wrapper = document.createElement("div");
                wrapper.className = "status-wrapper";

                const div = document.createElement("div");
                div.className = "status-circle";
                div.style.backgroundColor = status.color;
                div.textContent = status.code;
                div.title = status.label;

                const select = document.createElement("select");
                select.className = "status-select-overlay";

                statuses.forEach((s) => {
                  const option = document.createElement("option");
                  option.value = s.code;
                  option.textContent = s.label;
                  if (s.code === current) option.selected = true;
                  select.appendChild(option);
                });

                if (isHoliday) {
                  select.disabled = true;
                  div.style.backgroundColor = "#f9a8d4";
                  div.title = "Feiertag";
                  div.classList.add("holiday");
                }

                select.addEventListener("change", () => {
                  const selected = statuses.find(
                    (s) => s.code === select.value
                  );
                  div.textContent = selected.code;
                  div.style.backgroundColor = selected.color;
                  div.title = selected.label;

                  const date = getDateOfISOWeek(parseInt(week), year, d);

                  fetch("update_attendance.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                      user_id: user.id,
                      week: parseInt(week),
                      day: d,
                      status: selected.code
                    })
                  }).then(() => {
                    showNotification({
                      user_name: user.name,
                      date: date.toISOString(),
                      old_status: current,
                      new_status: selected.code
                    });
                  });
                });

                wrapper.appendChild(div);
                wrapper.appendChild(select);
                weekCell.appendChild(wrapper);
              }

              weekContainer.appendChild(weekCell);
            });

            row.appendChild(weekContainer);
            body.appendChild(row);
          });
        });
      });
    });
});
