import './bootstrap';
import { initDashboardCharts } from './dashboard-charts';
import { initInlineAttachers } from './inline-attacher';
import { initProjects } from './projects';
import { initSearchablePickers } from './searchable-picker';
import { initSidebar } from './sidebar';
import { initTasks } from './tasks';
import { initTheme } from './theme';
import { initWorkspaces } from './workspaces';

initTheme();
initSidebar();
initDashboardCharts();
initTasks();
initProjects();
initWorkspaces();
initSearchablePickers();
initInlineAttachers();
