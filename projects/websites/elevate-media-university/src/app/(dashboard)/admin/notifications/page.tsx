"use client";

import { DashboardLayout } from "@/components/layout/DashboardLayout";
import { useFetch } from "@/hooks/useFetch";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Bell, Check } from "lucide-react";
import { formatDateTime } from "@/lib/utils";

export default function AdminNotifications() {
  const { data, loading, refetch } = useFetch<{ notifications: { id: string; title: string; message: string; read: boolean; createdAt: string }[] }>("/api/notifications");

  const markAllRead = async () => {
    await fetch("/api/notifications", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ readAll: true }),
    });
    refetch();
  };

  const unreadCount = data?.notifications?.filter((n) => !n.read).length || 0;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Notifications</h1>
          {unreadCount > 0 && (
            <Button variant="outline" onClick={markAllRead}>
              <Check className="h-4 w-4" /> Mark all read ({unreadCount})
            </Button>
          )}
        </div>

        {loading ? (
          <div className="text-muted-foreground">Loading...</div>
        ) : data?.notifications?.length ? (
          <div className="space-y-2">
            {data.notifications.map((notif) => (
              <Card key={notif.id} className={notif.read ? "opacity-60" : ""}>
                <CardContent className="p-4">
                  <div className="flex items-start gap-3">
                    <Bell className={`h-4 w-4 shrink-0 mt-0.5 ${notif.read ? "text-muted-foreground" : "text-primary"}`} />
                    <div>
                      <p className={`text-sm ${notif.read ? "" : "font-semibold"}`}>{notif.title}</p>
                      <p className="text-sm text-muted-foreground">{notif.message}</p>
                      <p className="text-xs text-muted-foreground mt-1">{formatDateTime(notif.createdAt)}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        ) : (
          <Card>
            <CardContent className="py-8 text-center text-muted-foreground">No notifications.</CardContent>
          </Card>
        )}
      </div>
    </DashboardLayout>
  );
}
