/**
 * Modul JavaScript ES5: useCalendar.spec.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { afterEach, describe, expect, it, vi } from "vitest";
import { useCalendar } from "../../composables/useCalendar";

describe("useCalendar.formatRelativeDayLabel", function () {
  afterEach(function () {
    vi.useRealTimers();
  });

  it("retorna etiqueta de avui/today/hoy segons locale", function () {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 0, 15, 12, 0, 0));
    var cal = useCalendar();

    expect(cal.formatRelativeDayLabel("2026-01-15", "ca")).toBe("Avui");
    expect(cal.formatRelativeDayLabel("2026-01-15", "es")).toBe("Hoy");
    expect(cal.formatRelativeDayLabel("2026-01-15", "en")).toBe("Today");
  });

  it("retorna etiqueta d'ahir/yesterday/ayer segons locale", function () {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 0, 15, 12, 0, 0));
    var cal = useCalendar();

    expect(cal.formatRelativeDayLabel("2026-01-14", "ca")).toBe("Ahir");
    expect(cal.formatRelativeDayLabel("2026-01-14", "es-ES")).toBe("Ayer");
    expect(cal.formatRelativeDayLabel("2026-01-14", "en-US")).toBe("Yesterday");
  });

  it("retorna format curt DiaSetmana DD/MM per dies antics", function () {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2026, 0, 15, 12, 0, 0));
    var cal = useCalendar();

    expect(cal.formatRelativeDayLabel("2026-01-10", "ca")).toBe("Ds 10/1");
    expect(cal.formatRelativeDayLabel("2026-01-10", "es")).toBe("Sab 10/1");
    expect(cal.formatRelativeDayLabel("2026-01-10", "en")).toBe("Sat 10/1");
  });
});
