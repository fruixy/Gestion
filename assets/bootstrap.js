import { startStimulusApp } from '@symfony/stimulus-bundle';
import ProjectBoardController from './controllers/project_board_controllers.js';

export const app = startStimulusApp();

app.register('ProjectBoardController', ProjectBoardController);