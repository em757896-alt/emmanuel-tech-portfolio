import type { Metadata } from "next";
import { InfoPage } from "@/components/layout/InfoPage";
import { getInfoPage } from "@/lib/info-pages";

const content = getInfoPage("/students/affairs");

export const metadata: Metadata = { title: content.title, description: content.description };

export default function StudentAffairsPage() {
  return <InfoPage content={content} />;
}
