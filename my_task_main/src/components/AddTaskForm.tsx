import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card } from "@/components/ui/card";
import { Plus } from "lucide-react";

interface AddTaskFormProps {
  onAdd: (title: string, description: string) => void;
  loading: boolean;
}

export const AddTaskForm = ({ onAdd, loading }: AddTaskFormProps) => {
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (title.trim()) {
      onAdd(title.trim(), description.trim());
      setTitle("");
      setDescription("");
    }
  };

  return (
    <Card className="p-4 shadow-lg border-primary/20">
      <form onSubmit={handleSubmit} className="space-y-3">
        <Input
          placeholder="What needs to be done?"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          disabled={loading}
          className="font-medium"
        />
        <Textarea
          placeholder="Add a description (optional)"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          disabled={loading}
          rows={2}
        />
        <Button type="submit" disabled={loading || !title.trim()} className="w-full">
          <Plus className="w-4 h-4 mr-2" />
          Add Task
        </Button>
      </form>
    </Card>
  );
};
