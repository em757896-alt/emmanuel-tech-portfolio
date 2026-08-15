"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Megaphone } from "lucide-react";
import { formatDateTime } from "@/lib/utils";

export default function StudentAnnouncements() {
  const { data, loading } = useFetch<{ announcements: { id: string; title: string; content: string; target: string; createdAt: string; author: { name: string | null; role: string }; course: { name: string; code: string } | null }[] }>("/api/announcements");

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold">Announcements</h1>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.announcements?.length ? (
          <div className="space-y-4">
            {data.announcements.map((ann) => (
              <Card key={ann.id}>
                <CardContent className="p-6">
                  <div className="flex items-start gap-3">
                    <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                      <Megaphone className="h-5 w-5 text-primary" />
                    </div>
                    <div className="space-y-1 flex-1">
                      <div className="flex items-center gap-2 flex-wrap">
                        <h3 className="font-semibold">{ann.title}</h3>
                        {ann.course && <Badge variant="outline">{ann.course.code}</Badge>}
                        <Badge variant="secondary">{ann.target}</Badge>
                      </div>
                      <p className="text-sm text-muted-foreground whitespace-pre-wrap">{ann.content}</p>
                      <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        <span>By {ann.author.name || "Unknown"}</span>
                        <span>{formatDateTime(ann.createdAt)}</span>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">
              No announcements yet.
            </CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
