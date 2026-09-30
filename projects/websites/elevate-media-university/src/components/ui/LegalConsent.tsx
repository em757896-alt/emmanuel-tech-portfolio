"use client";

import Link from "next/link";

interface LegalConsentProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  mode?: "signup" | "signin";
  id?: string;
}

export function LegalConsent({ checked, onChange, mode = "signup", id = "legal-consent" }: LegalConsentProps) {
  return (
    <div className="flex items-start gap-3 rounded-lg border border-border bg-muted/40 p-3">
      <input
        id={id}
        name={id}
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
        required
        aria-describedby={`${id}-label`}
        className="mt-0.5 h-4 w-4 shrink-0 cursor-pointer accent-primary"
      />
      <label htmlFor={id} id={`${id}-label`} className="cursor-pointer text-xs leading-relaxed text-muted-foreground">
        {mode === "signup" ? (
          <>
            I have read and agree to the{" "}
            <Link href="/privacy" target="_blank" className="font-medium text-primary underline underline-offset-2 hover:text-primary/80">
              Privacy Policy
            </Link>{" "}
            and the{" "}
            <Link href="/terms" target="_blank" className="font-medium text-primary underline underline-offset-2 hover:text-primary/80">
              Terms of Use
            </Link>
            , and I consent to Elevate Media University processing my personal data as described in the Privacy Policy.
          </>
        ) : (
          <>
            I have read the{" "}
            <Link href="/privacy" target="_blank" className="font-medium text-primary underline underline-offset-2 hover:text-primary/80">
              Privacy Policy
            </Link>{" "}
            and the{" "}
            <Link href="/terms" target="_blank" className="font-medium text-primary underline underline-offset-2 hover:text-primary/80">
              Terms of Use
            </Link>
            , and consent to my personal data being processed when I sign in.
          </>
        )}
      </label>
    </div>
  );
}
