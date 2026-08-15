import type { Metadata } from "next";
import { InfoPage } from "@/components/layout/InfoPage";
import { getInfoPage } from "@/lib/info-pages";

const content = getInfoPage("/about/leadership");

export const metadata: Metadata = { title: content.title, description: content.description };

export default function LeadershipPage() {
  return <InfoPage content={content} />;
}
