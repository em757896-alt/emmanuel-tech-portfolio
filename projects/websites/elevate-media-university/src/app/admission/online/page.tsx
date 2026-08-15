import type { Metadata } from "next";
import { InfoPage } from "@/components/layout/InfoPage";
import { getInfoPage } from "@/lib/info-pages";

const content = getInfoPage("/admission/online");

export const metadata: Metadata = { title: content.title, description: content.description };

export default function OnlinePage() {
  return <InfoPage content={content} />;
}
