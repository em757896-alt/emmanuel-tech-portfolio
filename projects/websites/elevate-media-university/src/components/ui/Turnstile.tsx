"use client";

import { useEffect, useRef } from "react";

const SCRIPT_URL = "https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
const SITE_KEY = (process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY as string | undefined) ?? "";

declare global {
  interface Window {
    turnstile?: {
      render: (el: HTMLElement, opts: Record<string, unknown>) => string;
      reset: (widgetId?: string) => void;
      remove: (widgetId: string) => void;
    };
  }
}

export function isTurnstileConfigured(): boolean {
  return Boolean(SITE_KEY);
}

function loadTurnstile(): Promise<void> {
  return new Promise((resolve) => {
    const poll = setInterval(() => {
      if (window.turnstile) {
        clearInterval(poll);
        resolve();
      }
    }, 100);

    if (window.turnstile) {
      clearInterval(poll);
      resolve();
      return;
    }

    let script = document.querySelector<HTMLScriptElement>("script[data-turnstile]");
    if (!script) {
      script = document.createElement("script");
      script.src = SCRIPT_URL;
      script.async = true;
      script.defer = true;
      script.dataset.turnstile = "1";
      document.head.appendChild(script);
    }
  });
}

interface TurnstileProps {
  onVerify: (token: string) => void;
  onReset?: () => void;
  resetKey?: number;
}

export function Turnstile({ onVerify, onReset, resetKey = 0 }: TurnstileProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const widgetIdRef = useRef<string | null>(null);
  const verifyRef = useRef(onVerify);
  const resetRef = useRef(onReset);
  verifyRef.current = onVerify;
  resetRef.current = onReset;

  useEffect(() => {
    if (!SITE_KEY || !containerRef.current) return;

    let cancelled = false;
    loadTurnstile().then(() => {
      if (cancelled || !containerRef.current || !window.turnstile) return;
      widgetIdRef.current = window.turnstile.render(containerRef.current, {
        sitekey: SITE_KEY,
        theme: "light",
        callback: (token: string) => verifyRef.current(token),
        "expired-callback": () => resetRef.current?.(),
        "error-callback": () => resetRef.current?.(),
      });
    });

    return () => {
      cancelled = true;
      if (widgetIdRef.current && window.turnstile) {
        window.turnstile.remove(widgetIdRef.current);
        widgetIdRef.current = null;
      }
    };
  }, []);

  useEffect(() => {
    if (!SITE_KEY || !widgetIdRef.current || !window.turnstile || resetKey === 0) return;
    window.turnstile.reset(widgetIdRef.current);
  }, [resetKey]);

  if (!SITE_KEY) return null;

  return (
    <div className="flex justify-center py-1">
      <div ref={containerRef} />
    </div>
  );
}
