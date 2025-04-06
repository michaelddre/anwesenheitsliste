document.addEventListener("DOMContentLoaded", () => {
  const socket = io("http://localhost:3000");

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

  socket.on("attendanceUpdate", ({ user_id, week, day, new_status }) => {
    console.log("📡 Live-Update empfangen:", user_id, week, day, new_status);

    document.querySelectorAll(".month-body").forEach((body) => {
      const weeks = body.dataset.weeks.split(",");
      const weekIndex = weeks.indexOf(week.toString());
      if (weekIndex === -1) return;

      const row = Array.from(body.querySelectorAll(".user-row")).find(
        (r) => r.dataset.userId == user_id
      );
      if (!row) return;

      const weekCell = row.querySelectorAll(".week-cell")[weekIndex];
      if (!weekCell) return;

      const wrapper = weekCell.querySelectorAll(".status-wrapper")[day - 1];
      if (!wrapper) return;

      const circle = wrapper.querySelector(".status-circle");
      const select = wrapper.querySelector("select");

      const newStatus = statuses.find((s) => s.code === new_status) || statuses[0];
      circle.textContent = newStatus.code;
      circle.style.backgroundColor = newStatus.color;
      circle.title = newStatus.label;
      if (select) select.value = new_status;
    });
  });

  const getStatusLabel = (code) =>
    statuses.find((s) => s.code === code)?.label || "—";

  const getDateOfISOWeek = (week, year, weekday) => {
    const jan4 = new Date(year, 0, 4);
    const jan4Day = jan4.getDay() || 7;
    const mondayOfWeek1 = new Date(jan4);
    mondayOfWeek1.setDate(jan4.getDate() - jan4Day + 1);
    const resultDate = new Date(mondayOfWeek1);
    resultDate.setDate(mondayOfWeek1.getDate() + (week - 1) * 7 + (weekday - 1));
    return resultDate;
  };

  const shownNotifications = new Set();
  let lastLoginTime = null;
  let isCollapsed = false;

  const notificationsContainer = document.createElement("div");
  notificationsContainer.id = "notifications";
  notificationsContainer.style.position = "fixed";
  notificationsContainer.style.top = "20px";
  notificationsContainer.style.left = "20px";
  notificationsContainer.style.zIndex = "9999";
  notificationsContainer.style.maxWidth = "320px";
  notificationsContainer.style.background = "#f8fafc";
  notificationsContainer.style.border = "1px solid #cbd5e1";
  notificationsContainer.style.borderRadius = "8px";
  notificationsContainer.style.boxShadow = "0 4px 12px rgba(0,0,0,0.1)";
  notificationsContainer.style.overflow = "hidden";
  notificationsContainer.style.fontFamily = "sans-serif";

  const header = document.createElement("div");
  header.style.display = "flex";
  header.style.justifyContent = "space-between";
  header.style.alignItems = "center";
  header.style.background = "#1f2937";
  header.style.color = "white";
  header.style.padding = "8px 12px";
  header.style.cursor = "pointer";
  header.style.position = "relative";
  header.style.overflow = "visible";

  const title = document.createElement("span");
  title.textContent = "🔔 Benachrichtigungen";
  title.style.fontWeight = "bold";

  const toggleIcon = document.createElement("span");
  toggleIcon.textContent = "";
  toggleIcon.style.fontSize = "12px";

  const badge = document.createElement("span");
  badge.style.position = "absolute";
  badge.style.top = "2px";
  badge.style.right = "2px";
  badge.style.minWidth = "20px";
  badge.style.height = "20px";
  badge.style.padding = "0 6px";
  badge.style.borderRadius = "999px";
  badge.style.background = "#ef4444";
  badge.style.color = "white";
  badge.style.fontWeight = "bold";
  badge.style.fontSize = "12px";
  badge.style.display = "none";
  badge.style.alignItems = "center";
  badge.style.justifyContent = "center";
  badge.style.textAlign = "center";
  badge.style.lineHeight = "20px";

  header.appendChild(title);
  header.appendChild(toggleIcon);
  header.appendChild(badge);
  notificationsContainer.appendChild(header);

  const content = document.createElement("div");
  content.style.padding = "10px";
  content.style.display = "flex";
  content.style.flexDirection = "column";
  content.style.gap = "10px";

  const emptyMessage = document.createElement("div");
  emptyMessage.textContent = "🙌 Keine ungelesenen Benachrichtigungen";
  emptyMessage.style.color = "#64748b";
  emptyMessage.style.fontSize = "14px";
  emptyMessage.style.textAlign = "center";

  const clearBtn = document.createElement("button");
  clearBtn.textContent = "✅ Alle als gelesen";
  clearBtn.style.padding = "6px 10px";
  clearBtn.style.borderRadius = "6px";
  clearBtn.style.border = "none";
  clearBtn.style.background = "#4b5563";
  clearBtn.style.color = "white";
  clearBtn.style.cursor = "pointer";
  clearBtn.addEventListener("click", () => {
    content.querySelectorAll(".notification").forEach((n) => n.remove());
    fetch("mark_notifications_read.php", { method: "POST" });
    shownNotifications.clear();
    updateNotificationState();
  });

  const updateNotificationState = () => {
    const count = content.querySelectorAll(".notification").length;
    if (count > 0) {
      if (!content.contains(clearBtn)) content.prepend(clearBtn);
      emptyMessage.remove();
      title.textContent = "🔔 Benachrichtigungen";
      badge.textContent = count;
      badge.style.display = "flex";
    } else {
      if (content.contains(clearBtn)) clearBtn.remove();
      content.appendChild(emptyMessage);
      title.textContent = "✅ Keine Benachrichtigungen";
      badge.style.display = "none";
    }
  };

  header.addEventListener("click", () => {
    isCollapsed = !isCollapsed;
    content.style.display = isCollapsed ? "none" : "flex";
    toggleIcon.textContent = isCollapsed ? "" : "";
  });

  content.style.display = "flex";
  notificationsContainer.appendChild(content);
  document.body.appendChild(notificationsContainer);

  const createNotificationKey = (n) =>
    `${n.user_name}-${n.date}-${n.old_status}-${n.new_status}`;

  const showNotification = (n, isNewFromServer = false) => {
    const key = createNotificationKey(n);
    if (shownNotifications.has(key)) return;
    shownNotifications.add(key);

    const div = document.createElement("div");
    div.className = "notification";

    const formatted = new Date(n.date).toLocaleDateString("de-DE");
    div.textContent = `📝 ${n.changed_by_name} hat ${n.user_name}s Status am ${formatted} von ${getStatusLabel(n.old_status)} zu ${getStatusLabel(n.new_status)} geändert`;

    const created = new Date(n.created_at);
    const isOld = lastLoginTime && created < lastLoginTime;

    div.style.background = isOld ? "#e5e7eb" : "#fef08a";
    div.style.color = "#1f2937";
    div.style.padding = "10px 14px";
    div.style.borderRadius = "6px";
    div.style.boxShadow = "inset 0 0 2px rgba(0,0,0,0.1)";
    div.style.fontSize = "14px";
    div.style.animation = "fadeIn 0.3s ease-in-out";

    content.insertBefore(div, clearBtn.nextSibling);
    updateNotificationState();

    if (isNewFromServer && isCollapsed) {
      isCollapsed = false;
      content.style.display = "flex";
      toggleIcon.textContent = "▲";
    }
  };

  const pollNotifications = () => {
    fetch("get_notifications.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.last_login) lastLoginTime = new Date(data.last_login);
        data.notifications.forEach((n) => showNotification(n, true));
      });
  };

  setInterval(pollNotifications, 5000);

  // Anwesenheitsanzeige
  fetch("load_users.php")
    .then((res) => res.json())
    .then((users) => {
      document.querySelectorAll(".month-body").forEach((body) => {
        const weeks = body.dataset.weeks.split(",");
        const year = new Date().getFullYear();

        Promise.all(
          weeks.map((w) =>
            fetch(`get_attendance.php?week=${w}`).then((r) => r.json())
          )
        ).then((attendancePerWeek) => {
          users.forEach((user) => {
            const row = document.createElement("div");
            row.className = "user-row";
            row.dataset.userId = user.id;

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
                  const notificationData = {
                    user_name: user.name,
                    date: date.toISOString(),
                    old_status: current,
                    new_status: selected.code
                  };
                  const key = createNotificationKey(notificationData);
                  shownNotifications.add(key);

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
                    showNotification(notificationData, false);

                    socket.emit("attendanceChanged", {
                      user_id: user.id,
                      week: parseInt(week),
                      day: d,
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