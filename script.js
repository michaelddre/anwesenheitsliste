
let currentUserName = null;
let currentUserImage = null;

document.addEventListener("DOMContentLoaded", () => {
  fetch("get_current_user.php")
    .then((response) => response.json())
    .then((user) => {
      currentUserName = user.name;
      currentUserImage = user.image;

      document.getElementById("username").textContent = user.name;
      document.getElementById("user-avatar").src = `images/${user.image}`;
      document.getElementById("last-login-time").textContent = `Letzter Login: ${user.last_login}`;

      // Jetzt ist alles bekannt – Socket starten
      const socket = io("https://anwesenheitsliste.onrender.com/");

      // userJoined senden
      socket.emit("userJoined", {
        name: currentUserName,
        image: currentUserImage
      });

      // onlineUsers empfangen
      const onlineList = document.getElementById("online-avatar-list");
      socket.on("onlineUsers", (users) => {
        if (!onlineList) return;
        onlineList.innerHTML = "";

        users.forEach((user) => {
          const img = document.createElement("img");
          img.src = `images/${user.image}`;
          img.alt = user.name;
          img.title = user.name;
          img.className = "online-avatar";
          onlineList.appendChild(img);
        });
      });

      // attendanceUpdate empfangen
      socket.on("attendanceUpdate", ({ user_id, week, day, new_status }) => {
        // Deine bestehende Aktualisierungslogik hier...
      });

      // Und hier: wenn du später nochmal emitten willst, kannst du socket verwenden!
    });
});

    // ✅ Änderungen live anzeigen
    socket.on("attendanceUpdate", ({ user_id, week, day, new_status }) => {
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
 ;

  socket.on("attendanceUpdate", ({ user_id, week, day, new_status }) => {
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
  ;

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

  const getDateOfISOWeek = (week, year, weekday) => {
    const jan4 = new Date(year, 0, 4);
    const jan4Day = jan4.getDay() || 7;
    const mondayOfWeek1 = new Date(jan4);
    mondayOfWeek1.setDate(jan4.getDate() - jan4Day + 1);
    const resultDate = new Date(mondayOfWeek1);
    resultDate.setDate(mondayOfWeek1.getDate() + (week - 1) * 7 + (weekday - 1));
    return resultDate;
  };

  const shownNotifications = new Map();
  let lastLoginTime = null;
  const bubble = document.getElementById("notification-bubble");
  const badge = document.getElementById("notification-badge");

  const dropdown = document.createElement("div");
  dropdown.id = "notification-dropdown";
  Object.assign(dropdown.style, {
    position: "absolute",
    top: "50px",
    left: "0",
    background: "#1f2937",
    color: "white",
    borderRadius: "8px",
    padding: "10px 16px",
    boxShadow: "0 4px 12px rgba(0,0,0,0.2)",
    fontSize: "14px",
    minWidth: "240px",
    display: "none",
    zIndex: "1000",
    flexDirection: "column",
    gap: "10px"
  });

  bubble.style.position = "relative";
  bubble.appendChild(dropdown);

  const updateDropdown = () => {
    dropdown.innerHTML = "";
    if (shownNotifications.size === 0) {
      const empty = document.createElement("div");
      empty.textContent = "🙌 Keine ungelesenen Benachrichtigungen";
      empty.style.color = "#cbd5e1";
      dropdown.appendChild(empty);
      badge.style.display = "none";
    } else {
      for (const el of shownNotifications.values()) {
        dropdown.appendChild(el);
      }

      const clearBtn = document.createElement("button");
      clearBtn.textContent = "✅ Alle als gelesen";
      Object.assign(clearBtn.style, {
        padding: "6px 10px",
        borderRadius: "6px",
        border: "none",
        background: "#4b5563",
        color: "white",
        cursor: "pointer"
      });
      clearBtn.onclick = () => {
        shownNotifications.clear();
        updateDropdown();
        fetch("mark_notifications_read.php", { method: "POST" });
      };
      dropdown.appendChild(clearBtn);
      badge.style.display = "flex";
      badge.textContent = shownNotifications.size;
    }
  };

  bubble.addEventListener("click", () => {
    dropdown.style.display = dropdown.style.display === "none" ? "flex" : "none";
  });

  const createNotificationKey = (n) =>
    `${n.user_name}-${n.date}-${n.old_status}-${n.new_status}`;

  const showNotification = (n, isNewFromServer = false) => {
    if (n.changed_by_name === currentUserName) return;
    const key = createNotificationKey(n);
    if (shownNotifications.has(key)) return;

    const div = document.createElement("div");
    div.className = "notification";
    const formatted = new Date(n.date).toLocaleDateString("de-DE");
    div.textContent = `📌 ${n.changed_by_name} hat ${n.user_name}s Status am ${formatted} von ${getStatusLabel(n.old_status)} zu ${getStatusLabel(n.new_status)} geändert`;

    const created = new Date(n.created_at);
    const isOld = lastLoginTime && created < lastLoginTime;

    Object.assign(div.style, {
      background: isOld ? "#e5e7eb" : "#fef08a",
      color: "#1f2937",
      padding: "10px 14px",
      borderRadius: "6px",
      fontSize: "14px",
      boxShadow: "inset 0 0 2px rgba(0,0,0,0.1)"
    });

    shownNotifications.set(key, div);
    updateDropdown();
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
  updateDropdown();

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
                const status = statuses.find((s) => s.code === current) || statuses[0];

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
                    new_status: selected.code,
                    changed_by_name: currentUserName
                  };

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
