'use client'

import { useState } from 'react'
import { makeStyles, tokens } from '@fluentui/react-components'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { TimelineHeader } from './timeline-header'
import { ProjectTimelineSummary } from './project-timeline-summary'
import { MasterConstructionTimeline } from './master-construction-timeline'
import { CurrentPhaseDetails } from './current-phase-details'
import { PhaseOverviewCards } from './phase-overview-cards'
import { UpcomingMilestonesTable } from './upcoming-milestones-table'
import { TimelineActivityLog } from './timeline-activity-log'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    height: '100vh',
    backgroundColor: '#F5F5F0',
  },
  main: {
    display: 'flex',
    flexDirection: 'column',
    flex: 1,
    overflow: 'auto',
  },
  content: {
    flex: 1,
    paddingTop: '32px',
    paddingBottom: '48px',
    paddingLeft: '32px',
    paddingRight: '32px',
    maxWidth: '1600px',
    marginLeft: 'auto',
    marginRight: 'auto',
    width: '100%',
  },
  section: {
    marginBottom: '40px',
  },
})

export function ProjectTimelinePage() {
  const styles = useStyles()
  const [sidebarOpen, setSidebarOpen] = useState(true)

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} breadcrumb="Dashboard / Project Timeline" />
        <div className={styles.content}>
          <div className={styles.section}>
            <TimelineHeader />
          </div>
          <div className={styles.section}>
            <ProjectTimelineSummary />
          </div>
          <div className={styles.section}>
            <MasterConstructionTimeline />
          </div>
          <div className={styles.section}>
            <CurrentPhaseDetails />
          </div>
          <div className={styles.section}>
            <PhaseOverviewCards />
          </div>
          <div className={styles.section}>
            <UpcomingMilestonesTable />
          </div>
          <div className={styles.section}>
            <TimelineActivityLog />
          </div>
        </div>
      </div>
    </div>
  )
}
