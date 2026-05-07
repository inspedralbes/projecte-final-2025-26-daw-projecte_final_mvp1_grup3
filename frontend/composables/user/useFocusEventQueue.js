var FOCUS_PENDING_KEY = "loopy_focus_pending_events";

function readPendingEvents() {
  if (typeof localStorage === "undefined") {
    return [];
  }

  try {
    var raw = localStorage.getItem(FOCUS_PENDING_KEY);
    if (!raw) {
      return [];
    }
    var parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) {
      return [];
    }
    return parsed;
  } catch (e) {
    return [];
  }
}

function writePendingEvents(events) {
  if (typeof localStorage === "undefined") {
    return;
  }
  try {
    localStorage.setItem(FOCUS_PENDING_KEY, JSON.stringify(events || []));
  } catch (e) {}
}

export function enqueuePendingFocusEvent(payload) {
  if (!payload || !payload.habit_id) {
    return;
  }

  var events = readPendingEvents();
  events.push({
    habit_id: payload.habit_id,
    mode: payload.mode || null,
    minutes: payload.minutes || 0,
    event: payload.event || "update",
    data: payload.data || new Date().toISOString()
  });
  writePendingEvents(events);
}

export function flushPendingFocusEvents(socket) {
  if (!socket || !socket.connected) {
    return 0;
  }

  var events = readPendingEvents();
  if (events.length === 0) {
    return 0;
  }

  var sent = 0;
  var i;
  for (i = 0; i < events.length; i++) {
    socket.emit("habit_focus_update", events[i]);
    sent += 1;
  }

  writePendingEvents([]);
  return sent;
}
