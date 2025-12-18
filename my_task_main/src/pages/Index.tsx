import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { supabase } from "@/integrations/supabase/client";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { TaskCard } from "@/components/TaskCard";
import { AddTaskForm } from "@/components/AddTaskForm";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { LogOut, ListTodo } from "lucide-react";
import type { User, Session } from "@supabase/supabase-js";

interface Task {
  id: string;
  title: string;
  description: string | null;
  completed: boolean;
  created_at: string;
  user_id: string;
}

type FilterType = "all" | "active" | "completed";

const Index = () => {
  const [user, setUser] = useState<User | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<FilterType>("all");
  const navigate = useNavigate();
  const { toast } = useToast();

  useEffect(() => {
    let subscription: any;

    try {
      const result = supabase.auth.onAuthStateChange((event, session) => {
        setSession(session);
        setUser(session?.user ?? null);
        if (!session) {
          navigate("/auth");
        }
      });

      subscription = result.data?.subscription;
    } catch (err) {
      console.error("onAuthStateChange failed:", err);
      toast({
        title: "Auth subscription error",
        description: "Unable to subscribe to auth state changes. Check your network or Supabase configuration.",
        variant: "destructive",
      });
    }

    supabase.auth
      .getSession()
      .then(({ data: { session } }) => {
        setSession(session);
        setUser(session?.user ?? null);
        if (!session) {
          navigate("/auth");
        }
      })
      .catch((err) => {
        console.error("getSession failed:", err);
        toast({
          title: "Network error",
          description: "Unable to reach the auth server. Check your internet connection or VITE_SUPABASE_URL.",
          variant: "destructive",
        });
      });

    return () => {
      if (subscription && typeof subscription.unsubscribe === "function") {
        subscription.unsubscribe();
      }
    };
  }, [navigate, toast]);

  useEffect(() => {
    if (user) {
      fetchTasks();
    }
  }, [user]);

  const fetchTasks = async () => {
    try {
      setLoading(true);
      if (!navigator.onLine) {
        throw new Error("Offline: cannot fetch tasks");
      }
      const { data, error } = await supabase
        .from("tasks")
        .select("*")
        .order("created_at", { ascending: false });

      if (error) throw error;
      setTasks(data || []);
    } catch (error: any) {
      toast({
        title: "Error loading tasks",
        description: error.message,
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const addTask = async (title: string, description: string) => {
    if (!user) return;

    try {
      const { data, error } = await supabase
        .from("tasks")
        .insert([
          {
            title,
            description: description || null,
            user_id: user.id,
            completed: false,
          },
        ])
        .select()
        .single();

      if (error) throw error;

      setTasks([data, ...tasks]);
      toast({
        title: "Task added",
        description: "Your task has been created successfully.",
      });
    } catch (error: any) {
      toast({
        title: "Error adding task",
        description: error.message,
        variant: "destructive",
      });
    }
  };

  const toggleTask = async (id: string, completed: boolean) => {
    try {
      const { error } = await supabase
        .from("tasks")
        .update({ completed })
        .eq("id", id);

      if (error) throw error;

      setTasks(tasks.map((task) => (task.id === id ? { ...task, completed } : task)));
      toast({
        title: completed ? "Task completed" : "Task reopened",
        description: completed ? "Great job!" : "Task marked as active.",
      });
    } catch (error: any) {
      toast({
        title: "Error updating task",
        description: error.message,
        variant: "destructive",
      });
    }
  };

  const deleteTask = async (id: string) => {
    try {
      const { error } = await supabase.from("tasks").delete().eq("id", id);

      if (error) throw error;

      setTasks(tasks.filter((task) => task.id !== id));
      toast({
        title: "Task deleted",
        description: "Task has been removed.",
      });
    } catch (error: any) {
      toast({
        title: "Error deleting task",
        description: error.message,
        variant: "destructive",
      });
    }
  };

  const updateTask = async (id: string, title: string, description: string) => {
    try {
      const { error } = await supabase
        .from("tasks")
        .update({ title, description: description || null })
        .eq("id", id);

      if (error) throw error;

      setTasks(
        tasks.map((task) =>
          task.id === id ? { ...task, title, description } : task
        )
      );
      toast({
        title: "Task updated",
        description: "Your changes have been saved.",
      });
    } catch (error: any) {
      toast({
        title: "Error updating task",
        description: error.message,
        variant: "destructive",
      });
    }
  };

  const handleLogout = async () => {
    try {
      const { error } = await supabase.auth.signOut();
      if (error) throw error;
      navigate("/auth");
    } catch (err: any) {
      console.error("signOut failed:", err);
      toast({
        title: "Logout failed",
        description: err instanceof Error ? err.message : String(err),
        variant: "destructive",
      });
    }
  };

  const filteredTasks = tasks.filter((task) => {
    if (filter === "active") return !task.completed;
    if (filter === "completed") return task.completed;
    return true;
  });

  const taskStats = {
    total: tasks.length,
    active: tasks.filter((t) => !t.completed).length,
    completed: tasks.filter((t) => t.completed).length,
  };

  if (loading && !user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-primary-glow mb-4 animate-pulse">
            <ListTodo className="w-8 h-8 text-primary-foreground" />
          </div>
          <p className="text-muted-foreground">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-primary/5 via-background to-accent/5">
      <div className="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <header className="mb-8">
          <div className="flex items-center justify-between mb-6">
            <div className="flex items-center gap-3">
              <div className="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-glow shadow-lg">
                <ListTodo className="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <h1 className="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
                  TaskFlow
                </h1>
                <p className="text-sm text-muted-foreground">
                  Welcome back, {user?.email?.split("@")[0]}
                </p>
              </div>
            </div>
            <Button variant="outline" onClick={handleLogout} size="sm">
              <LogOut className="w-4 h-4 mr-2" />
              Logout
            </Button>
          </div>

          <div className="grid grid-cols-3 gap-4 mb-6">
            <div className="bg-card rounded-lg p-4 shadow-soft">
              <p className="text-sm text-muted-foreground mb-1">Total</p>
              <p className="text-2xl font-bold">{taskStats.total}</p>
            </div>
            <div className="bg-card rounded-lg p-4 shadow-soft">
              <p className="text-sm text-muted-foreground mb-1">Active</p>
              <p className="text-2xl font-bold text-primary">{taskStats.active}</p>
            </div>
            <div className="bg-card rounded-lg p-4 shadow-soft">
              <p className="text-sm text-muted-foreground mb-1">Done</p>
              <p className="text-2xl font-bold text-success">{taskStats.completed}</p>
            </div>
          </div>

          <AddTaskForm onAdd={addTask} loading={loading} />
        </header>

        <div className="space-y-4">
          <Tabs value={filter} onValueChange={(v) => setFilter(v as FilterType)}>
            <TabsList className="w-full grid grid-cols-3">
              <TabsTrigger value="all">All ({taskStats.total})</TabsTrigger>
              <TabsTrigger value="active">Active ({taskStats.active})</TabsTrigger>
              <TabsTrigger value="completed">Done ({taskStats.completed})</TabsTrigger>
            </TabsList>
          </Tabs>

          <div className="space-y-3">
            {loading ? (
              <div className="text-center py-12">
                <p className="text-muted-foreground">Loading tasks...</p>
              </div>
            ) : filteredTasks.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-muted-foreground">
                  {filter === "all"
                    ? "No tasks yet. Create one to get started!"
                    : filter === "active"
                    ? "No active tasks. Great job!"
                    : "No completed tasks yet."}
                </p>
              </div>
            ) : (
              filteredTasks.map((task) => (
                <TaskCard
                  key={task.id}
                  task={task}
                  onToggle={toggleTask}
                  onDelete={deleteTask}
                  onUpdate={updateTask}
                />
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Index;
