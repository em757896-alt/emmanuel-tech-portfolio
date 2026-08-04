import type { Metadata } from "next";
import { InfoPage } from "@/components/layout/InfoPage";
import { getInfoPage } from "@/lib/info-pages";

const content = getInfoPage("/admission/international");

export const metadata: Metadata = { title: content.title, description: content.description };

export default function InternationalPage() {
  return <InfoPage content={content} />;
}
