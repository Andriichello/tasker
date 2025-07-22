import { defineStore } from 'pinia';
import {
  indexTasks,
  showTask,
  storeTask,
  updateTask,
  destroyTask
} from '../api/services/tasks';
import type { Task } from '../api/models/task';
import type { StoreTaskRequest } from '../api/models/storeTaskRequest';
import type { UpdateTaskRequest } from '../api/models/updateTaskRequest';

export const useTasksStore = defineStore('tasks', {
  state: () => ({
    tasks: [] as Task[],
    task: null as Task | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    getTasks: (state) => state.tasks,
    getTask: (state) => state.task,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getError: (state) => state.error,
  },

  actions: {
    async fetchTasks(search?: string) {
      this.loading = true;
      this.error = null;

      try {
        const options = search ? { params: { search } } : undefined;
        const response = await indexTasks(options);
        this.tasks = response.data.data || [];
        return this.tasks;
      } catch (error) {
        this.error = 'Failed to fetch tasks';
        return [];
      } finally {
        this.loading = false;
      }
    },

    async fetchTask(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await showTask(id);
        this.task = response.data.data || null;
        return this.task;
      } catch (error) {
        this.error = `Failed to fetch task with ID ${id}`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    async createTask(taskData: StoreTaskRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await storeTask(taskData);
        const newTask = response.data.data;

        if (newTask) {
          this.tasks = [...this.tasks, newTask];
          this.task = newTask;
        }

        return newTask;
      } catch (error) {
        this.error = 'Failed to create task';
        return null;
      } finally {
        this.loading = false;
      }
    },

    async updateTask(id: number, taskData: UpdateTaskRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await updateTask(id, taskData);
        const updatedTask = response.data.data;

        if (updatedTask) {
          // Update the task in the tasks array
          this.tasks = this.tasks.map(task =>
            task.id === id ? updatedTask : task
          );

          // Update the current task if it's the one being updated
          if (this.task && this.task.id === id) {
            this.task = updatedTask;
          }
        }

        return updatedTask;
      } catch (error) {
        this.error = `Failed to update task with ID ${id}`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    async deleteTask(id: number) {
      this.loading = true;
      this.error = null;

      try {
        await destroyTask(id);

        // Remove the task from the tasks array
        this.tasks = this.tasks.filter(task => task.id !== id);

        // Clear the current task if it's the one being deleted
        if (this.task && this.task.id === id) {
          this.task = null;
        }

        return true;
      } catch (error) {
        this.error = `Failed to delete task with ID ${id}`;
        return false;
      } finally {
        this.loading = false;
      }
    },

    clearTask() {
      this.task = null;
    },

    clearError() {
      this.error = null;
    }
  },
});
